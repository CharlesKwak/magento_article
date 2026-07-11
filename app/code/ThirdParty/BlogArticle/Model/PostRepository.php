<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\Data\PostInterface;
use ThirdParty\BlogArticle\Api\Data\PostInterfaceFactory;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class PostRepository implements PostRepositoryInterface
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PostInterfaceFactory
     */
    private $dataFactory;

    /**
     * @var PostFilter
     */
    private $postFilter;

    /**
     * @var UrlKeyGenerator
     */
    private $urlKeyGenerator;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    public function __construct(
        PostFactory $postFactory,
        CollectionFactory $collectionFactory,
        PostInterfaceFactory $dataFactory,
        PostFilter $postFilter,
        UrlKeyGenerator $urlKeyGenerator,
        CategoryFactory $categoryFactory
    ) {
        $this->postFactory = $postFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataFactory = $dataFactory;
        $this->postFilter = $postFilter;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($postId, $activeOnly = true)
    {
        $post = $this->postFactory->create()->load((int) $postId);
        if (!$post->getId() || ($activeOnly && !(int) $post->getIsActive())) {
            throw new NoSuchEntityException(
                __('The blog post with ID "%1" does not exist or is disabled.', $postId)
            );
        }
        return $this->toDataModel($post);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUrlKey($urlKey, $activeOnly = true)
    {
        $urlKey = trim((string) $urlKey);
        $post = $this->postFactory->create()->load($urlKey, 'url_key');
        if (!$post->getId() || ($activeOnly && !(int) $post->getIsActive())) {
            throw new NoSuchEntityException(
                __('The blog post with URL key "%1" does not exist or is disabled.', $urlKey)
            );
        }
        return $this->toDataModel($post);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($page = 1, $pageSize = 10, $search = null, $categoryId = null)
    {
        $page = max(1, (int) $page);
        $pageSize = max(1, min(100, (int) $pageSize));
        $categoryId = $categoryId !== null && $categoryId !== '' ? (int) $categoryId : null;

        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applySearch($collection, $search !== null ? (string) $search : null);
        $this->postFilter->applyCategoryId($collection, $categoryId);
        $collection->setOrder('creation_time', 'DESC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        $items = [];
        foreach ($collection as $post) {
            $items[] = $this->toDataModel($post);
        }
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getListTotalCount($search = null, $categoryId = null)
    {
        $categoryId = $categoryId !== null && $categoryId !== '' ? (int) $categoryId : null;
        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applySearch($collection, $search !== null ? (string) $search : null);
        $this->postFilter->applyCategoryId($collection, $categoryId);
        return (int) $collection->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function save(PostInterface $post)
    {
        $model = $this->postFactory->create();
        if ($post->getPostId()) {
            $model->load((int) $post->getPostId());
            if (!$model->getId()) {
                throw new NoSuchEntityException(
                    __('The blog post with ID "%1" does not exist.', $post->getPostId())
                );
            }
        }

        $title = trim((string) $post->getTitle());
        $content = trim((string) $post->getContent());
        if ($title === '' || $content === '') {
            throw new LocalizedException(__('Title and content are required.'));
        }

        $urlSource = trim((string) $post->getUrlKey());
        if ($urlSource === '') {
            $urlSource = $title;
        }
        $urlKey = $this->urlKeyGenerator->generate(
            $urlSource,
            $model->getId() ? (int) $model->getId() : null
        );

        $categoryId = $post->getCategoryId();
        if ($categoryId) {
            $category = $this->categoryFactory->create()->load((int) $categoryId);
            if (!$category->getId()) {
                throw new LocalizedException(__('Category with ID "%1" does not exist.', $categoryId));
            }
        } else {
            $categoryId = null;
        }

        $isActive = $post->getIsActive();
        if ($isActive === null) {
            $isActive = 1;
        }

        $model->setTitle($title);
        $model->setContent($content);
        $model->setUrlKey($urlKey);
        $model->setIsActive((int) $isActive ? 1 : 0);
        $model->setCategoryId($categoryId);

        try {
            $model->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the blog post: %1', $e->getMessage()), $e);
        }

        return $this->toDataModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($postId)
    {
        $model = $this->postFactory->create()->load((int) $postId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The blog post with ID "%1" does not exist.', $postId));
        }
        try {
            $model->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the blog post: %1', $e->getMessage()), $e);
        }
        return true;
    }

    /**
     * @param Post $post
     * @return PostInterface
     */
    private function toDataModel(Post $post): PostInterface
    {
        /** @var PostInterface $data */
        $data = $this->dataFactory->create();
        $data->setPostId((int) $post->getId());
        $data->setTitle((string) $post->getTitle());
        $data->setUrlKey((string) $post->getUrlKey());
        $data->setContent((string) $post->getContent());
        $data->setIsActive((int) $post->getIsActive());
        $data->setCategoryId($post->getCategoryId() ? (int) $post->getCategoryId() : null);
        $data->setCreationTime((string) $post->getCreationTime());
        $data->setUpdateTime((string) $post->getUpdateTime());
        return $data;
    }
}
