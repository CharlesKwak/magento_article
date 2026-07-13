<?php
namespace ThirdParty\BlogArticle\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\Seo\HreflangBuilder;
use ThirdParty\BlogArticle\Model\TagFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

class Article extends Template implements IdentityInterface
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
     * @var HreflangBuilder
     */
    private $hreflangBuilder;

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
        HreflangBuilder $hreflangBuilder,
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
        $this->hreflangBuilder = $hreflangBuilder;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $blogName = $this->config->getBlogName();
        $metaTitle = $this->config->getListMetaTitle();
        if ($metaTitle === '') {
            $metaTitle = $blogName;
        }
        if ($metaTitle !== '') {
            $this->pageConfig->getTitle()->set($metaTitle);
        }
        $metaDescription = $this->config->getListMetaDescription();
        if ($metaDescription !== '') {
            $this->pageConfig->setDescription($metaDescription);
        }
        $canonical = $this->getCanonicalUrl();
        if ($canonical !== '') {
            $this->pageConfig->addRemotePageAsset(
                $canonical,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->getBaseUrl(),
                ]
            );
            $breadcrumbs->addCrumb(
                'blog',
                [
                    'label' => $blogName,
                    'title' => $blogName,
                    'link' => $this->getUrl('blog/index/index'),
                ]
            );
            $heading = $this->getFilterHeading();
            if ($heading !== '') {
                $breadcrumbs->addCrumb(
                    'filter',
                    [
                        'label' => $heading,
                        'title' => $heading,
                    ]
                );
            }
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPosts()
    {
        if ($this->posts === null) {
            $collection = $this->collectionFactory->create();
            $this->postFilter->applyActiveOnly($collection);
            $this->postFilter->applyPublishedOnly($collection);
            $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
            $this->postFilter->applySearch($collection, $this->getSearchQuery());
            $this->postFilter->applyCategoryId($collection, $this->getCategoryIdFilter());
            $this->postFilter->applyTagId($collection, $this->getTagIdFilter());
            $this->postFilter->applyAuthorKey($collection, $this->getAuthorKeyFilter());
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
     * Author slug or name from /blog/author/{slug} or ?author=
     */
    public function getAuthorKeyFilter(): string
    {
        return trim((string) $this->getRequest()->getParam('author', ''));
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
        $urlKey = trim($urlKey);
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/category/' . $urlKey]);
        }
        return $this->getUrl('blog/index/index');
    }

    public function getTagFilterUrl(string $urlKey): string
    {
        $urlKey = trim($urlKey);
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/tag/' . $urlKey]);
        }
        return $this->getUrl('blog/index/index');
    }

    /**
     * Heading when a category or tag filter is active.
     */
    public function getFilterHeading(): string
    {
        $catKey = $this->getCategoryKeyFilter();
        if ($catKey !== '') {
            $category = $this->categoryFactory->create()->load($catKey, 'url_key');
            if ($category->getId()) {
                return (string) __('Category: %1', $category->getName());
            }
        }
        $categoryId = $this->getCategoryIdFilter();
        if ($categoryId) {
            $category = $this->categoryFactory->create()->load($categoryId);
            if ($category->getId()) {
                return (string) __('Category: %1', $category->getName());
            }
        }
        $tagKey = $this->getTagKeyFilter();
        if ($tagKey !== '') {
            $tag = $this->tagFactory->create()->load($tagKey, 'url_key');
            if ($tag->getId()) {
                return (string) __('Tag: %1', $tag->getName());
            }
        }
        $tagId = $this->getTagIdFilter();
        if ($tagId) {
            $tag = $this->tagFactory->create()->load($tagId);
            if ($tag->getId()) {
                return (string) __('Tag: %1', $tag->getName());
            }
        }
        $authorKey = $this->getAuthorKeyFilter();
        if ($authorKey !== '') {
            $label = str_replace('-', ' ', $authorKey);
            return (string) __('Author: %1', $label);
        }
        return '';
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
        $params = [];
        $cat = $this->getCategoryKeyFilter();
        if ($cat !== '') {
            $params['category'] = $cat;
        }
        $tag = $this->getTagKeyFilter();
        if ($tag !== '') {
            $params['tag'] = $tag;
        }
        $author = $this->getAuthorKeyFilter();
        if ($author !== '') {
            $params['author'] = $author;
        }
        return $this->getUrl('blog/rss/feed', $params);
    }

    /**
     * Canonical URL for the current list/filter view.
     */
    public function getCanonicalUrl(): string
    {
        $cat = $this->getCategoryKeyFilter();
        if ($cat !== '') {
            return $this->getUrl('', ['_direct' => 'blog/category/' . $cat]);
        }
        $tag = $this->getTagKeyFilter();
        if ($tag !== '') {
            return $this->getUrl('', ['_direct' => 'blog/tag/' . $tag]);
        }
        $author = $this->getAuthorKeyFilter();
        if ($author !== '') {
            return $this->getUrl('', ['_direct' => 'blog/author/' . $author]);
        }
        return $this->getUrl('blog/index/index');
    }

    /**
     * Relative path used for multi-store hreflang generation.
     */
    public function getHreflangPath(): string
    {
        $cat = $this->getCategoryKeyFilter();
        if ($cat !== '') {
            return 'blog/category/' . $cat;
        }
        $tag = $this->getTagKeyFilter();
        if ($tag !== '') {
            return 'blog/tag/' . $tag;
        }
        $author = $this->getAuthorKeyFilter();
        if ($author !== '') {
            return 'blog/author/' . $author;
        }
        return 'blog/';
    }

    /**
     * @return array<int, array{hreflang:string,href:string}>
     */
    public function getHreflangLinks(): array
    {
        return $this->hreflangBuilder->buildForPath($this->getHreflangPath(), null);
    }

    public function isLazyLoadImagesEnabled(): bool
    {
        return $this->config->isLazyLoadImagesEnabled();
    }

    public function getListPageTitle(): string
    {
        $heading = $this->getFilterHeading();
        if ($heading !== '') {
            return $heading;
        }
        $metaTitle = $this->config->getListMetaTitle();
        if ($metaTitle !== '') {
            return $metaTitle;
        }
        return $this->config->getBlogName();
    }

    public function getListPageDescription(): string
    {
        $metaDescription = $this->config->getListMetaDescription();
        if ($metaDescription !== '') {
            return $metaDescription;
        }
        $heading = $this->getFilterHeading();
        if ($heading !== '') {
            return (string) __('Browse %1 on %2', $heading, $this->config->getBlogName());
        }
        return (string) __('Latest posts from %1', $this->config->getBlogName());
    }

    /**
     * Open Graph / Twitter meta for the blog list / filter pages.
     *
     * @return array<int, array{property?:string,name?:string,content:string}>
     */
    public function getSocialMetaTags(): array
    {
        $title = $this->getListPageTitle();
        $description = $this->getListPageDescription();
        $url = $this->getCanonicalUrl();
        $tags = [
            ['property' => 'og:type', 'content' => 'website'],
            ['property' => 'og:title', 'content' => $title],
            ['property' => 'og:description', 'content' => $description],
            ['property' => 'og:url', 'content' => $url],
            ['property' => 'og:site_name', 'content' => $this->config->getBlogName()],
            ['name' => 'twitter:card', 'content' => 'summary'],
            ['name' => 'twitter:title', 'content' => $title],
            ['name' => 'twitter:description', 'content' => $description],
        ];
        // First post featured image as list preview when available.
        foreach ($this->getPosts() as $post) {
            $image = $this->getFeaturedImageUrl($post);
            if ($image !== '') {
                $tags[] = ['property' => 'og:image', 'content' => $image];
                $tags[] = ['name' => 'twitter:image', 'content' => $image];
                break;
            }
        }
        return $tags;
    }

    /**
     * JSON-LD: CollectionPage + BreadcrumbList for list/filter views.
     */
    public function getJsonLd(): string
    {
        $blogName = $this->config->getBlogName();
        $listUrl = $this->getUrl('blog/index/index');
        $pageUrl = $this->getCanonicalUrl();
        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => (string) __('Home'),
                    'item' => $this->getBaseUrl(),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $blogName,
                    'item' => $listUrl,
                ],
            ],
        ];
        $heading = $this->getFilterHeading();
        if ($heading !== '') {
            $breadcrumb['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $heading,
                'item' => $pageUrl,
            ];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'CollectionPage',
                    'name' => $this->getListPageTitle(),
                    'description' => $this->getListPageDescription(),
                    'url' => $pageUrl,
                    'isPartOf' => [
                        '@type' => 'Blog',
                        'name' => $blogName,
                        'url' => $listUrl,
                    ],
                ],
                $breadcrumb,
            ],
        ];
        return (string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [Post::CACHE_TAG];
        foreach ($this->getPosts() as $post) {
            $identities[] = Post::CACHE_TAG . '_' . $post->getId();
        }
        return array_unique($identities);
    }
}
