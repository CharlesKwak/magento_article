<?php
namespace ThirdParty\BlogArticle\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class Article extends Template
{
    public const DEFAULT_PAGE_SIZE = 5;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Collection|null
     */
    private $posts;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Active posts only, newest first, paginated.
     *
     * @return Collection
     */
    public function getPosts()
    {
        if ($this->posts === null) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('is_active', 1);
            $collection->setOrder('creation_time', 'DESC');
            $collection->setPageSize($this->getPageSize());
            $collection->setCurPage($this->getCurrentPage());
            $this->posts = $collection;
        }

        return $this->posts;
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
        return $size > 0 ? $size : self::DEFAULT_PAGE_SIZE;
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
        return $this->getUrl('blog/index/index', $params);
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
