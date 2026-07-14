<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Block\Sidebar;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class Recent extends Template
{
    private CollectionFactory $collectionFactory;
    private PostFilter $postFilter;
    private Config $config;
    private StoreManagerInterface $storeManager;
    private ?Collection $posts = null;

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

    public function isEnabled(): bool
    {
        return $this->config->isSidebarEnabled();
    }

    public function getBlockTitle(): string
    {
        return (string) __('Recent Posts');
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
            $this->postFilter->applyDefaultSort($collection);
            $collection->setPageSize($this->config->getSidebarRecentCount());
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

    protected function _toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
