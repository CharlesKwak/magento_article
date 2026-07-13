<?php
namespace ThirdParty\BlogArticle\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\PostProductLink;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

/**
 * Related blog posts on catalog product view.
 */
class RelatedPosts extends Template
{
    private $registry;
    private $postProductLink;
    private $collectionFactory;
    private $postFilter;
    private $config;
    private $storeManager;
    private $posts;

    public function __construct(
        Context $context,
        Registry $registry,
        PostProductLink $postProductLink,
        CollectionFactory $collectionFactory,
        PostFilter $postFilter,
        Config $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->postProductLink = $postProductLink;
        $this->collectionFactory = $collectionFactory;
        $this->postFilter = $postFilter;
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->config->isProductRelatedPostsEnabled();
    }

    public function getBlockTitle(): string
    {
        return (string) __('Related Blog Posts');
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        if ($this->posts !== null) {
            return $this->posts;
        }
        $this->posts = [];
        if (!$this->isEnabled()) {
            return $this->posts;
        }
        $product = $this->registry->registry('current_product');
        if (!$product instanceof Product || !(int) $product->getId()) {
            return $this->posts;
        }
        $postIds = $this->postProductLink->getPostIdsForProduct((int) $product->getId());
        if (!$postIds) {
            return $this->posts;
        }
        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applyPublishedOnly($collection);
        $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
        $collection->addFieldToFilter('post_id', ['in' => $postIds]);
        $this->postFilter->applyDefaultSort($collection);
        $collection->setPageSize($this->config->getProductRelatedPostsLimit());
        foreach ($collection as $post) {
            $this->posts[] = $post;
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

    protected function _toHtml()
    {
        if (!$this->isEnabled() || !$this->getPosts()) {
            return '';
        }
        return parent::_toHtml();
    }
}
