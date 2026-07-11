<?php
namespace ThirdParty\BlogArticle\Model;

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

    public function __construct(
        PostFactory $postFactory,
        CollectionFactory $collectionFactory,
        PostInterfaceFactory $dataFactory,
        PostFilter $postFilter
    ) {
        $this->postFactory = $postFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataFactory = $dataFactory;
        $this->postFilter = $postFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($postId)
    {
        $post = $this->postFactory->create()->load((int) $postId);
        if (!$post->getId() || !(int) $post->getIsActive()) {
            throw new NoSuchEntityException(
                __('The blog post with ID "%1" does not exist or is disabled.', $postId)
            );
        }
        return $this->toDataModel($post);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUrlKey($urlKey)
    {
        $urlKey = trim((string) $urlKey);
        $post = $this->postFactory->create()->load($urlKey, 'url_key');
        if (!$post->getId() || !(int) $post->getIsActive()) {
            throw new NoSuchEntityException(
                __('The blog post with URL key "%1" does not exist or is disabled.', $urlKey)
            );
        }
        return $this->toDataModel($post);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($page = 1, $pageSize = 10, $search = null)
    {
        $page = max(1, (int) $page);
        $pageSize = max(1, min(100, (int) $pageSize));

        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applySearch($collection, $search !== null ? (string) $search : null);
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
    public function getListTotalCount($search = null)
    {
        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applySearch($collection, $search !== null ? (string) $search : null);
        return (int) $collection->getSize();
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
        $data->setCreationTime((string) $post->getCreationTime());
        $data->setUpdateTime((string) $post->getUpdateTime());
        return $data;
    }
}
