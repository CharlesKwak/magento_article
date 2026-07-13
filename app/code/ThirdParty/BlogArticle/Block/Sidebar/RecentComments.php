<?php
namespace ThirdParty\BlogArticle\Block\Sidebar;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;

class RecentComments extends Template
{
    private $commentCollectionFactory;
    private $postFactory;
    private $config;
    private $storeManager;
    private $items;
    private $postCache = [];

    public function __construct(
        Context $context,
        CommentCollectionFactory $commentCollectionFactory,
        PostFactory $postFactory,
        Config $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->postFactory = $postFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->config->isSidebarEnabled()
            && $this->config->isCommentsEnabled()
            && $this->config->isRecentCommentsSidebarEnabled();
    }

    public function getBlockTitle(): string
    {
        return (string) __('Recent Comments');
    }

    /**
     * @return array<int, array{author:string,excerpt:string,post_title:string,url:string,created:string}>
     */
    public function getComments(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }
        $this->items = [];
        if (!$this->isEnabled()) {
            return $this->items;
        }

        $collection = $this->commentCollectionFactory->create();
        $collection->addFieldToFilter('is_approved', 1);
        $collection->setOrder('creation_time', 'DESC');
        $collection->setPageSize($this->config->getRecentCommentsSidebarCount());
        $collection->setCurPage(1);

        $storeId = (int) $this->storeManager->getStore()->getId();
        foreach ($collection as $comment) {
            $postId = (int) $comment->getPostId();
            $post = $this->loadVisiblePost($postId, $storeId);
            if (!$post) {
                continue;
            }
            $content = trim(preg_replace('/\s+/', ' ', strip_tags((string) $comment->getContent())) ?? '');
            if (function_exists('mb_substr')) {
                $excerpt = mb_substr($content, 0, 80);
            } else {
                $excerpt = substr($content, 0, 80);
            }
            if (strlen($content) > strlen($excerpt)) {
                $excerpt .= '…';
            }
            $urlKey = trim((string) $post->getUrlKey());
            $url = $urlKey !== ''
                ? $this->getUrl('', ['_direct' => 'blog/' . $urlKey])
                : $this->getUrl('blog/post/view', ['id' => $postId]);

            $this->items[] = [
                'author' => (string) $comment->getAuthorName(),
                'excerpt' => $excerpt,
                'post_title' => (string) $post->getTitle(),
                'url' => $url,
                'created' => (string) $comment->getCreationTime(),
            ];
        }
        return $this->items;
    }

    private function loadVisiblePost(int $postId, int $storeId)
    {
        if ($postId <= 0) {
            return null;
        }
        if (array_key_exists($postId, $this->postCache)) {
            return $this->postCache[$postId];
        }
        $post = $this->postFactory->create()->load($postId);
        if (!$post->getId() || !(int) $post->getIsActive()) {
            $this->postCache[$postId] = null;
            return null;
        }
        $postStore = $post->getStoreId() !== null && $post->getStoreId() !== ''
            ? (int) $post->getStoreId()
            : 0;
        if ($postStore > 0 && $storeId > 0 && $postStore !== $storeId) {
            $this->postCache[$postId] = null;
            return null;
        }
        $published = $post->getPublishedAt();
        if ($published) {
            $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
            if ((string) $published > $now) {
                $this->postCache[$postId] = null;
                return null;
            }
        }
        $this->postCache[$postId] = $post;
        return $post;
    }

    protected function _toHtml()
    {
        if (!$this->isEnabled() || !$this->getComments()) {
            return '';
        }
        return parent::_toHtml();
    }
}
