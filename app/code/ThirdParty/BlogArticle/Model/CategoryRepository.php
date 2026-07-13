<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\CategoryRepositoryInterface;
use ThirdParty\BlogArticle\Api\Data\CategoryInterface;
use ThirdParty\BlogArticle\Api\Data\CategoryInterfaceFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryInterfaceFactory
     */
    private $dataFactory;

    /**
     * @var CategoryUrlKeyGenerator
     */
    private $urlKeyGenerator;

    /**
     * @var PostCollectionFactory
     */
    private $postCollectionFactory;

    public function __construct(
        CategoryFactory $categoryFactory,
        CollectionFactory $collectionFactory,
        CategoryInterfaceFactory $dataFactory,
        CategoryUrlKeyGenerator $urlKeyGenerator,
        PostCollectionFactory $postCollectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataFactory = $dataFactory;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($categoryId, $activeOnly = true)
    {
        $category = $this->categoryFactory->create()->load((int) $categoryId);
        if (!$category->getId() || ($activeOnly && !(int) $category->getIsActive())) {
            throw new NoSuchEntityException(
                __('The category with ID "%1" does not exist or is disabled.', $categoryId)
            );
        }
        return $this->toDataModel($category);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUrlKey($urlKey, $activeOnly = true)
    {
        $urlKey = trim((string) $urlKey);
        $category = $this->categoryFactory->create()->load($urlKey, 'url_key');
        if (!$category->getId() || ($activeOnly && !(int) $category->getIsActive())) {
            throw new NoSuchEntityException(
                __('The category with URL key "%1" does not exist or is disabled.', $urlKey)
            );
        }
        return $this->toDataModel($category);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($activeOnly = true)
    {
        $collection = $this->collectionFactory->create();
        if ($activeOnly) {
            $collection->addFieldToFilter('is_active', 1);
        }
        $collection->setOrder('name', 'ASC');
        $items = [];
        foreach ($collection as $category) {
            $items[] = $this->toDataModel($category);
        }
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CategoryInterface $category)
    {
        $model = $this->categoryFactory->create();
        if ($category->getCategoryId()) {
            $model->load((int) $category->getCategoryId());
            if (!$model->getId()) {
                throw new NoSuchEntityException(
                    __('The category with ID "%1" does not exist.', $category->getCategoryId())
                );
            }
        }

        $name = trim((string) $category->getName());
        if ($name === '') {
            throw new LocalizedException(__('Name is required.'));
        }

        $urlSource = trim((string) $category->getUrlKey());
        if ($urlSource === '') {
            $urlSource = $name;
        }
        $urlKey = $this->urlKeyGenerator->generate(
            $urlSource,
            $model->getId() ? (int) $model->getId() : null
        );

        $isActive = $category->getIsActive();
        if ($isActive === null) {
            $isActive = 1;
        }

        $model->setName($name);
        $model->setUrlKey($urlKey);
        $description = $category->getDescription();
        $model->setData(
            'description',
            $description !== null && trim((string) $description) !== ''
                ? trim((string) $description)
                : null
        );
        $model->setIsActive((int) $isActive ? 1 : 0);

        try {
            $model->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the category: %1', $e->getMessage()), $e);
        }

        return $this->toDataModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($categoryId)
    {
        $model = $this->categoryFactory->create()->load((int) $categoryId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The category with ID "%1" does not exist.', $categoryId));
        }

        $posts = $this->postCollectionFactory->create();
        $posts->addFieldToFilter('category_id', (int) $categoryId);
        if ($posts->getSize() > 0) {
            throw new LocalizedException(
                __('Cannot delete category with assigned posts. Reassign or clear posts first.')
            );
        }

        try {
            $model->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the category: %1', $e->getMessage()), $e);
        }
        return true;
    }

    /**
     * @param Category $category
     * @return CategoryInterface
     */
    private function toDataModel(Category $category): CategoryInterface
    {
        /** @var CategoryInterface $data */
        $data = $this->dataFactory->create();
        $data->setCategoryId((int) $category->getId());
        $data->setName((string) $category->getName());
        $data->setUrlKey((string) $category->getUrlKey());
        $desc = $category->getData('description');
        $data->setDescription($desc !== null && (string) $desc !== '' ? (string) $desc : null);
        $data->setIsActive((int) $category->getIsActive());
        $data->setCreationTime((string) $category->getCreationTime());
        $data->setUpdateTime((string) $category->getUpdateTime());
        return $data;
    }
}
