<?php
namespace ThirdParty\BlogArticle\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

/**
 * CMS / page builder widget: recent blog posts (optional category filter).
 */
class Posts extends Template implements BlockInterface
{
    protected $_template = 'ThirdParty_BlogArticle::widget/posts.phtml';

    private $collectionFactory;
    private $postFilter;
    private $config;
    private $storeManager;
    private $posts;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        PostFilter $postFilter,
        Config $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->postFilter = $postFilter;
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getWidgetTitle(): string
    {
        $title = trim((string) $this->getData('title'));
        if ($title !== '') {
            return $title;
        }
        return (string) __('Recent Posts');
    }

    public function getPostCount(): int
    {
        $n = (int) $this->getData('post_count');
        if ($n < 1) {
            return 5;
        }
        return min(20, $n);
    }

    public function getCategoryIdFilter(): ?int
    {
        $id = (int) $this->getData('category_id');
        return $id > 0 ? $id : null;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts()
    {
        if ($this->posts === null) {
            $collection = $this->collectionFactory->create();
            $this->postFilter->applyActiveOnly($collection);
            $this->postFilter->applyPublishedOnly($collection);
            $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
            $this->postFilter->applyCategoryId($collection, $this->getCategoryIdFilter());
            $this->postFilter->applyDefaultSort($collection);
            $collection->setPageSize($this->getPostCount());
            $collection->setCurPage(1);
            $this->posts = $collection;
        }
        return $this->posts;
    }

    public function getPostUrl(Post $post): string
    {
        $urlKey = trim((string) $post->getUrlKey());
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/' . $urlKey]);
        }
        return $this->getUrl('blog/post/view', ['id' => (int) $post->getId()]);
    }

    public function getBlogListUrl(): string
    {
        return $this->getUrl('blog/index/index');
    }

    public function getBlogName(): string
    {
        return $this->config->getBlogName();
    }
}
