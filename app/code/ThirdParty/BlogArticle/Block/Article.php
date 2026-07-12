<?php
namespace ThirdParty\BlogArticle\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\TagFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

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
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var TagCollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var PostTagLink
     */
    private $postTagLink;

    /**
     * @var FeaturedImageUploader
     */
    private $imageUploader;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Collection|null
     */
    private $posts;

    /**
     * @var array
     */
    private $categoryNameCache = [];

    /**
     * @var array
     */
    private $tagNameCache = [];

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        PostFilter $postFilter,
        Config $config,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryFactory $categoryFactory,
        TagCollectionFactory $tagCollectionFactory,
        TagFactory $tagFactory,
        PostTagLink $postTagLink,
        FeaturedImageUploader $imageUploader,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->postFilter = $postFilter;
        $this->config = $config;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->tagFactory = $tagFactory;
        $this->postTagLink = $postTagLink;
        $this->imageUploader = $imageUploader;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getPosts()
    {
        if ($this->posts === null) {
            $collection = $this->collectionFactory->create();
            $this->postFilter->applyActiveOnly($collection);
            $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
            $this->postFilter->applySearch($collection, $this->getSearchQuery());
            $this->postFilter->applyCategoryId($collection, $this->getCategoryIdFilter());
            $this->postFilter->applyTagId($collection, $this->getTagIdFilter());
            $this->postFilter->applyDefaultSort($collection);
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
     * Category filter from ?cat=url_key or ?category_id=
     *
     * @return int|null
     */
    public function getCategoryIdFilter(): ?int
    {
        $categoryId = (int) $this->getRequest()->getParam('category_id', 0);
        if ($categoryId > 0) {
            return $categoryId;
        }

        $catKey = trim((string) $this->getRequest()->getParam('cat', ''));
        if ($catKey === '') {
            return null;
        }

        $category = $this->categoryFactory->create()->load($catKey, 'url_key');
        if ($category->getId() && (int) $category->getIsActive()) {
            return (int) $category->getId();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getCategoryKeyFilter(): string
    {
        return trim((string) $this->getRequest()->getParam('cat', ''));
    }

    /**
     * Tag filter from ?tag=url_key or ?tag_id=
     *
     * @return int|null
     */
    public function getTagIdFilter(): ?int
    {
        $tagId = (int) $this->getRequest()->getParam('tag_id', 0);
        if ($tagId > 0) {
            return $tagId;
        }
        $tagKey = trim((string) $this->getRequest()->getParam('tag', ''));
        if ($tagKey === '') {
            return null;
        }
        $tag = $this->tagFactory->create()->load($tagKey, 'url_key');
        if ($tag->getId() && (int) $tag->getIsActive()) {
            return (int) $tag->getId();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getTagKeyFilter(): string
    {
        return trim((string) $this->getRequest()->getParam('tag', ''));
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Category\Collection
     */
    public function getActiveCategories()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Tag\Collection
     */
    public function getActiveTags()
    {
        $collection = $this->tagCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    /**
     * @param Post $post
     * @return string
     */
    public function getCategoryName(Post $post): string
    {
        $categoryId = (int) $post->getCategoryId();
        if ($categoryId <= 0) {
            return '';
        }
        if (!array_key_exists($categoryId, $this->categoryNameCache)) {
            $category = $this->categoryFactory->create()->load($categoryId);
            $this->categoryNameCache[$categoryId] = $category->getId()
                ? (string) $category->getName()
                : '';
        }
        return $this->categoryNameCache[$categoryId];
    }

    /**
     * @param Post $post
     * @return string[]
     */
    public function getTagNames(Post $post): array
    {
        $names = [];
        foreach ($this->postTagLink->getTagIdsForPost((int) $post->getId()) as $tagId) {
            if (!array_key_exists($tagId, $this->tagNameCache)) {
                $tag = $this->tagFactory->create()->load($tagId);
                $this->tagNameCache[$tagId] = $tag->getId() ? (string) $tag->getName() : '';
            }
            if ($this->tagNameCache[$tagId] !== '') {
                $names[] = $this->tagNameCache[$tagId];
            }
        }
        return $names;
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
     * @param string $urlKey
     * @return string
     */
    public function getCategoryFilterUrl(string $urlKey): string
    {
        return $this->getUrl('blog/index/index', ['cat' => $urlKey]);
    }

    public function getTagFilterUrl(string $urlKey): string
    {
        return $this->getUrl('blog/index/index', ['tag' => $urlKey]);
    }

    /**
     * @return bool
     */
    public function hasPager(): bool
    {
        return $this->getLastPageNumber() > 1;
    }

    /**
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
        $manual = trim((string) $post->getExcerpt());
        if ($manual !== '') {
            return $manual;
        }
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

    /**
     * @param Post $post
     * @return string
     */
    public function getFeaturedImageUrl(Post $post): string
    {
        return $this->imageUploader->resolveUrl($post->getFeaturedImage() ? (string) $post->getFeaturedImage() : null);
    }

    /**
     * @return string
     */
    public function getRssUrl(): string
    {
        return $this->getUrl('blog/rss/feed');
    }
}
