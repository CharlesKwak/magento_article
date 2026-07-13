<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\Data\TagInterface;
use ThirdParty\BlogArticle\Api\Data\TagInterfaceFactory;
use ThirdParty\BlogArticle\Api\TagRepositoryInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;

class TagRepository implements TagRepositoryInterface
{
    private $tagFactory;
    private $collectionFactory;
    private $dataFactory;
    private $urlKeyGenerator;
    private $postTagLink;

    public function __construct(
        TagFactory $tagFactory,
        CollectionFactory $collectionFactory,
        TagInterfaceFactory $dataFactory,
        TagUrlKeyGenerator $urlKeyGenerator,
        PostTagLink $postTagLink
    ) {
        $this->tagFactory = $tagFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataFactory = $dataFactory;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->postTagLink = $postTagLink;
    }

    public function getById($tagId, $activeOnly = true)
    {
        $tag = $this->tagFactory->create()->load((int) $tagId);
        if (!$tag->getId() || ($activeOnly && !(int) $tag->getIsActive())) {
            throw new NoSuchEntityException(__('The tag with ID "%1" does not exist or is disabled.', $tagId));
        }
        return $this->toDataModel($tag);
    }

    public function getByUrlKey($urlKey, $activeOnly = true)
    {
        $urlKey = trim((string) $urlKey);
        $tag = $this->tagFactory->create()->load($urlKey, 'url_key');
        if (!$tag->getId() || ($activeOnly && !(int) $tag->getIsActive())) {
            throw new NoSuchEntityException(__('The tag with URL key "%1" does not exist or is disabled.', $urlKey));
        }
        return $this->toDataModel($tag);
    }

    public function getList($activeOnly = true)
    {
        $collection = $this->collectionFactory->create();
        if ($activeOnly) {
            $collection->addFieldToFilter('is_active', 1);
        }
        $collection->setOrder('name', 'ASC');
        $items = [];
        foreach ($collection as $tag) {
            $items[] = $this->toDataModel($tag);
        }
        return $items;
    }

    public function save(TagInterface $tag)
    {
        $model = $this->tagFactory->create();
        if ($tag->getTagId()) {
            $model->load((int) $tag->getTagId());
            if (!$model->getId()) {
                throw new NoSuchEntityException(__('The tag with ID "%1" does not exist.', $tag->getTagId()));
            }
        }

        $name = trim((string) $tag->getName());
        if ($name === '') {
            throw new LocalizedException(__('Name is required.'));
        }

        $urlSource = trim((string) $tag->getUrlKey());
        if ($urlSource === '') {
            $urlSource = $name;
        }
        $urlKey = $this->urlKeyGenerator->generate(
            $urlSource,
            $model->getId() ? (int) $model->getId() : null
        );

        $isActive = $tag->getIsActive();
        if ($isActive === null) {
            $isActive = 1;
        }

        $model->setName($name);
        $model->setUrlKey($urlKey);
        $description = $tag->getDescription();
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
            throw new CouldNotSaveException(__('Could not save the tag: %1', $e->getMessage()), $e);
        }
        return $this->toDataModel($model);
    }

    public function deleteById($tagId)
    {
        $model = $this->tagFactory->create()->load((int) $tagId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The tag with ID "%1" does not exist.', $tagId));
        }
        if ($this->postTagLink->countPostsForTag((int) $tagId) > 0) {
            throw new LocalizedException(__('Cannot delete tag with assigned posts. Remove tag from posts first.'));
        }
        try {
            $model->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the tag: %1', $e->getMessage()), $e);
        }
        return true;
    }

    private function toDataModel(Tag $tag): TagInterface
    {
        /** @var TagInterface $data */
        $data = $this->dataFactory->create();
        $data->setTagId((int) $tag->getId());
        $data->setName((string) $tag->getName());
        $data->setUrlKey((string) $tag->getUrlKey());
        $desc = $tag->getData('description');
        $data->setDescription($desc !== null && (string) $desc !== '' ? (string) $desc : null);
        $data->setIsActive((int) $tag->getIsActive());
        $data->setCreationTime((string) $tag->getCreationTime());
        $data->setUpdateTime((string) $tag->getUpdateTime());
        return $data;
    }
}
