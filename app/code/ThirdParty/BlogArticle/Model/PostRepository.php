<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Api\Data\PostInterface;
use ThirdParty\BlogArticle\Api\Data\PostInterfaceFactory;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class PostRepository implements PostRepositoryInterface
{
    private $postFactory;
    private $collectionFactory;
    private $dataFactory;
    private $postFilter;
    private $urlKeyGenerator;
    private $categoryFactory;
    private $postTagLink;
    private $tagFactory;
    private $storeManager;

    public function __construct(
        PostFactory $postFactory,
        CollectionFactory $collectionFactory,
        PostInterfaceFactory $dataFactory,
        PostFilter $postFilter,
        UrlKeyGenerator $urlKeyGenerator,
        CategoryFactory $categoryFactory,
        PostTagLink $postTagLink,
        TagFactory $tagFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->postFactory = $postFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataFactory = $dataFactory;
        $this->postFilter = $postFilter;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->categoryFactory = $categoryFactory;
        $this->postTagLink = $postTagLink;
        $this->tagFactory = $tagFactory;
        $this->storeManager = $storeManager;
    }

    public function getById($postId, $activeOnly = true)
    {
        $post = $this->postFactory->create()->load((int) $postId);
        if (!$post->getId() || ($activeOnly && !$this->isPubliclyVisible($post))) {
            throw new NoSuchEntityException(
                __('The blog post with ID "%1" does not exist or is disabled.', $postId)
            );
        }
        return $this->toDataModel($post);
    }

    public function getByUrlKey($urlKey, $activeOnly = true)
    {
        $urlKey = trim((string) $urlKey);
        $post = $this->postFactory->create()->load($urlKey, 'url_key');
        if (!$post->getId() || ($activeOnly && !$this->isPubliclyVisible($post))) {
            throw new NoSuchEntityException(
                __('The blog post with URL key "%1" does not exist or is disabled.', $urlKey)
            );
        }
        return $this->toDataModel($post);
    }

    public function getList($page = 1, $pageSize = 10, $search = null, $categoryId = null, $tagId = null, $author = null)
    {
        $page = max(1, (int) $page);
        $pageSize = max(1, min(100, (int) $pageSize));
        $categoryId = $categoryId !== null && $categoryId !== '' ? (int) $categoryId : null;
        $tagId = $tagId !== null && $tagId !== '' ? (int) $tagId : null;
        $author = $author !== null && $author !== '' ? (string) $author : null;

        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applyPublishedOnly($collection);
        $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
        $this->postFilter->applySearch($collection, $search !== null ? (string) $search : null);
        $this->postFilter->applyCategoryId($collection, $categoryId);
        $this->postFilter->applyTagId($collection, $tagId);
        $this->postFilter->applyAuthorKey($collection, $author);
        $this->postFilter->applyDefaultSort($collection);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        $items = [];
        foreach ($collection as $post) {
            $items[] = $this->toDataModel($post);
        }
        return $items;
    }

    public function getListTotalCount($search = null, $categoryId = null, $tagId = null, $author = null)
    {
        $categoryId = $categoryId !== null && $categoryId !== '' ? (int) $categoryId : null;
        $tagId = $tagId !== null && $tagId !== '' ? (int) $tagId : null;
        $author = $author !== null && $author !== '' ? (string) $author : null;
        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applyPublishedOnly($collection);
        $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
        $this->postFilter->applySearch($collection, $search !== null ? (string) $search : null);
        $this->postFilter->applyCategoryId($collection, $categoryId);
        $this->postFilter->applyTagId($collection, $tagId);
        $this->postFilter->applyAuthorKey($collection, $author);
        return (int) $collection->getSize();
    }

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

        $tagIds = $post->getTagIds();
        if ($tagIds === null) {
            $tagIds = $model->getId() ? $this->postTagLink->getTagIdsForPost((int) $model->getId()) : [];
        }
        $normalizedTagIds = [];
        foreach ((array) $tagIds as $tagId) {
            $tagId = (int) $tagId;
            if ($tagId <= 0) {
                continue;
            }
            $tag = $this->tagFactory->create()->load($tagId);
            if (!$tag->getId()) {
                throw new LocalizedException(__('Tag with ID "%1" does not exist.', $tagId));
            }
            $normalizedTagIds[] = $tagId;
        }

        $isActive = $post->getIsActive();
        if ($isActive === null) {
            $isActive = 1;
        }

        $model->setTitle($title);
        $model->setContent($content);
        $model->setUrlKey($urlKey);
        if ($post->getAuthor() !== null) {
            $model->setAuthor(trim((string) $post->getAuthor()) ?: null);
        }
        if ($post->getFeaturedImage() !== null) {
            $model->setFeaturedImage(trim((string) $post->getFeaturedImage()) ?: null);
        }
        if ($post->getMetaTitle() !== null) {
            $model->setMetaTitle(trim((string) $post->getMetaTitle()) ?: null);
        }
        if ($post->getMetaDescription() !== null) {
            $model->setMetaDescription(trim((string) $post->getMetaDescription()) ?: null);
        }
        if ($post->getExcerpt() !== null) {
            $model->setExcerpt(trim((string) $post->getExcerpt()) ?: null);
        }
        if ($post->getPublishedAt() !== null) {
            $publishedAt = trim((string) $post->getPublishedAt());
            $model->setPublishedAt($publishedAt !== '' ? $publishedAt : null);
        }
        if ($post->getStoreId() !== null) {
            $sid = (int) $post->getStoreId();
            $model->setStoreId($sid > 0 ? $sid : null);
        }
        $model->setIsActive((int) $isActive ? 1 : 0);
        $model->setCategoryId($categoryId);

        try {
            $model->save();
            $this->postTagLink->setTagsForPost((int) $model->getId(), $normalizedTagIds);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the blog post: %1', $e->getMessage()), $e);
        }

        return $this->toDataModel($model);
    }

    public function deleteById($postId)
    {
        $model = $this->postFactory->create()->load((int) $postId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The blog post with ID "%1" does not exist.', $postId));
        }
        try {
            $this->postTagLink->setTagsForPost((int) $postId, []);
            $model->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the blog post: %1', $e->getMessage()), $e);
        }
        return true;
    }

    public function getRelated($postId, $limit = 3)
    {
        $limit = max(1, min(20, (int) $limit));
        $source = $this->postFactory->create()->load((int) $postId);
        if (!$source->getId()) {
            throw new NoSuchEntityException(__('The blog post with ID "%1" does not exist.', $postId));
        }

        $build = function (?int $categoryId) use ($postId, $limit) {
            $collection = $this->collectionFactory->create();
            $this->postFilter->applyActiveOnly($collection);
            $this->postFilter->applyPublishedOnly($collection);
            $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
            $collection->addFieldToFilter('post_id', ['neq' => (int) $postId]);
            if ($categoryId) {
                $collection->addFieldToFilter('category_id', $categoryId);
            }
            $this->postFilter->applyDefaultSort($collection);
            $collection->setPageSize($limit);
            $items = [];
            foreach ($collection as $post) {
                $items[] = $this->toDataModel($post);
            }
            return $items;
        };

        $categoryId = $source->getCategoryId() ? (int) $source->getCategoryId() : null;
        $items = $build($categoryId);
        if (!$items && $categoryId) {
            $items = $build(null);
        }
        return $items;
    }

    /**
     * @param Post $post
     * @return bool
     */
    private function isPubliclyVisible(Post $post): bool
    {
        if (!(int) $post->getIsActive()) {
            return false;
        }
        $publishedAt = $post->getPublishedAt();
        if (!$publishedAt) {
            return true;
        }
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();
        $pub = strtotime((string) $publishedAt . ' UTC');
        return !$pub || $pub <= $now;
    }

    private function toDataModel(Post $post): PostInterface
    {
        /** @var PostInterface $data */
        $data = $this->dataFactory->create();
        $data->setPostId((int) $post->getId());
        $data->setTitle((string) $post->getTitle());
        $data->setAuthor($post->getAuthor() ? (string) $post->getAuthor() : null);
        $data->setUrlKey((string) $post->getUrlKey());
        $data->setContent((string) $post->getContent());
        $data->setExcerpt($post->getExcerpt() ? (string) $post->getExcerpt() : null);
        $data->setFeaturedImage($post->getFeaturedImage() ? (string) $post->getFeaturedImage() : null);
        $data->setMetaTitle($post->getMetaTitle() ? (string) $post->getMetaTitle() : null);
        $data->setMetaDescription($post->getMetaDescription() ? (string) $post->getMetaDescription() : null);
        $data->setIsActive((int) $post->getIsActive());
        $data->setViewCount((int) $post->getData('view_count'));
        $data->setCategoryId($post->getCategoryId() ? (int) $post->getCategoryId() : null);
        $data->setStoreId($post->getStoreId() ? (int) $post->getStoreId() : null);
        $data->setTagIds($this->postTagLink->getTagIdsForPost((int) $post->getId()));
        $data->setCreationTime((string) $post->getCreationTime());
        $data->setUpdateTime((string) $post->getUpdateTime());
        $data->setPublishedAt($post->getPublishedAt() ? (string) $post->getPublishedAt() : null);
        return $data;
    }
}
