<?php
namespace ThirdParty\BlogArticle\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class Article extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PostFilter
     */
    private $postFilter;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Collection|null
     */
    private $posts;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        PostFilter $postFilter,
        Config $config,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->postFilter = $postFilter;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Active posts only, newest first, optional search, paginated.
     *
     * @return Collection
     */
    public function getPosts()
    {
        if ($this->posts === null) {
            $collection = $this->collectionFactory->create();
            $this->postFilter->applyActiveOnly($collection);
            $this->postFilter->applySearch($collection, $this->getSearchQuery());
            $collection->setOrder('creation_time', 'DESC');
            $collection->setPageSize($this->getPageSize());
            $collection->setCurPage($this->getCurrentPage());
            $this->posts = $collection;
        }

        return $this->posts;
    }

    /**
     * @return string
     */
    public function getSearchQuery(): string
    {
        return trim((string) $this->getRequest()->getParam('q', ''));
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        $page = (int) $this->getRequest()->getParam('p', 1);
        return max(1, $page);
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        $size = (int) $this->getData('page_size');
        if ($size > 0) {
            return $size;
        }
        return $this->config->getPageSize();
    }

    /**
     * @return int
     */
    public function getLastPageNumber(): int
    {
        return (int) $this->getPosts()->getLastPageNumber();
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return (int) $this->getPosts()->getSize();
    }

    /**
     * @param int $page
     * @return string
     */
    public function getPageUrl(int $page): string
    {
        $params = ['_current' => true, '_use_rewrite' => true];
        if ($page > 1) {
            $params['p'] = $page;
        } else {
            $params['p'] = null;
        }
        $q = $this->getSearchQuery();
        if ($q !== '') {
            $params['q'] = $q;
        }
        return $this->getUrl('blog/index/index', $params);
    }

    /**
     * @return string
     */
    public function getSearchFormAction(): string
    {
        return $this->getUrl('blog/index/index');
    }

    /**
     * @return bool
     */
    public function hasPager(): bool
    {
        return $this->getLastPageNumber() > 1;
    }

    /**
     * Prefer clean URL /blog/{url_key} when available.
     *
     * @param Post $post
     * @return string
     */
    public function getPostUrl(Post $post): string
    {
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/' . $urlKey]);
        }

        return $this->getUrl('blog/post/view', ['id' => (int) $post->getId()]);
    }

    /**
     * @param Post $post
     * @param int $length
     * @return string
     */
    public function getExcerpt(Post $post, int $length = 200): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->getContent())) ?? '');
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text) <= $length) {
                return $text;
            }
            return rtrim(mb_substr($text, 0, $length)) . '…';
        }

        if (strlen($text) <= $length) {
            return $text;
        }
        return rtrim(substr($text, 0, $length)) . '...';
    }
}
