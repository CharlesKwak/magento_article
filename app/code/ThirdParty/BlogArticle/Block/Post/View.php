<?php
namespace ThirdParty\BlogArticle\Block\Post;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\CommentSpamGuard;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\PostProductLink;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use ThirdParty\BlogArticle\Model\Seo\HreflangBuilder;
use ThirdParty\BlogArticle\Model\TagFactory;

class View extends Template implements IdentityInterface
{
    private $postFactory;
    private $postRepository;
    private $postTagLink;
    private $postProductLink;
    private $productRepository;
    private $tagFactory;
    private $categoryFactory;
    private $imageUploader;
    private $commentRepository;
    private $config;
    private $formKey;
    private $dateTime;
    private $postFilter;
    private $postCollectionFactory;
    private $storeManager;
    private $post;
    private $related;
    private $relatedProducts;
    private $neighbors;
    private $tagNameCache = [];
    private $tagMetaCache = [];
    private $hreflangBuilder;
    /** @var array<int, array{id:string,level:int,text:string}>|null */
    private $tocItems;
    /** @var string|null prepared body HTML cache */
    private $preparedContentHtml;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        PostRepositoryInterface $postRepository,
        PostTagLink $postTagLink,
        PostProductLink $postProductLink,
        ProductRepositoryInterface $productRepository,
        TagFactory $tagFactory,
        CategoryFactory $categoryFactory,
        FeaturedImageUploader $imageUploader,
        CommentRepositoryInterface $commentRepository,
        Config $config,
        FormKey $formKey,
        DateTime $dateTime,
        PostFilter $postFilter,
        PostCollectionFactory $postCollectionFactory,
        StoreManagerInterface $storeManager,
        HreflangBuilder $hreflangBuilder,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->postRepository = $postRepository;
        $this->postTagLink = $postTagLink;
        $this->postProductLink = $postProductLink;
        $this->productRepository = $productRepository;
        $this->tagFactory = $tagFactory;
        $this->categoryFactory = $categoryFactory;
        $this->imageUploader = $imageUploader;
        $this->commentRepository = $commentRepository;
        $this->config = $config;
        $this->formKey = $formKey;
        $this->dateTime = $dateTime;
        $this->postFilter = $postFilter;
        $this->postCollectionFactory = $postCollectionFactory;
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
        $this->addBreadcrumbs();
        $canonical = $this->getCanonicalUrl();
        if ($canonical !== '') {
            $this->pageConfig->addRemotePageAsset(
                $canonical,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
        $robots = $this->getRobotsContent();
        if ($robots !== '') {
            $this->pageConfig->setRobots($robots);
        }
        return $this;
    }

    /**
     * Robots meta content for the post (default INDEX,FOLLOW).
     */
    public function getRobotsContent(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        $robots = strtoupper(trim((string) $post->getData('meta_robots')));
        if ($robots === '') {
            return 'INDEX,FOLLOW';
        }
        return $robots;
    }

    public function isLazyLoadImagesEnabled(): bool
    {
        return $this->config->isLazyLoadImagesEnabled();
    }

    /**
     * Optional amphtml URL for discovery (external AMP layer). Empty when disabled.
     */
    public function getAmpHtmlUrl(): string
    {
        if (!$this->config->isAmpHtmlEnabled()) {
            return '';
        }
        $pattern = $this->config->getAmpHtmlUrlPattern();
        if ($pattern === '') {
            return '';
        }
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        $urlKey = trim((string) $post->getUrlKey());
        $baseUrl = rtrim($this->getBaseUrl(), '/') . '/';
        $url = str_replace(
            ['{base_url}', '{url_key}', '{post_id}'],
            [$baseUrl, $urlKey, (string) (int) $post->getId()],
            $pattern
        );
        return trim($url);
    }

    private function addBreadcrumbs(): void
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if (!$breadcrumbs) {
            return;
        }
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->getBaseUrl(),
            ]
        );
        $blogName = $this->config->getBlogName();
        $breadcrumbs->addCrumb(
            'blog',
            [
                'label' => $blogName,
                'title' => $blogName,
                'link' => $this->getListUrl(),
            ]
        );
        $post = $this->getPost();
        if ($post) {
            $breadcrumbs->addCrumb(
                'post',
                [
                    'label' => $post->getTitle(),
                    'title' => $post->getTitle(),
                ]
            );
        }
    }

    /**
     * @return Post|null
     */
    public function getPost()
    {
        if ($this->post !== null) {
            return $this->post;
        }

        $post = $this->postFactory->create();
        $urlKey = (string) $this->getRequest()->getParam('url_key', '');
        $id = (int) $this->getRequest()->getParam('id', 0);

        if ($urlKey !== '') {
            $post->load($urlKey, 'url_key');
        } elseif ($id) {
            $post->load($id);
        }

        if (!$post->getId() || !(int) $post->getIsActive()) {
            $this->post = null;
            return null;
        }
        $publishedAt = $post->getPublishedAt();
        if ($publishedAt) {
            $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();
            $pub = strtotime((string) $publishedAt . ' UTC');
            if ($pub && $pub > $now) {
                $this->post = null;
                return null;
            }
        }

        $this->post = $post;
        return $this->post;
    }

    /**
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface[]
     */
    public function getRelatedPosts(): array
    {
        if ($this->related !== null) {
            return $this->related;
        }
        $post = $this->getPost();
        if (!$post) {
            $this->related = [];
            return $this->related;
        }
        try {
            $limit = $this->config->getRelatedPostsLimit();
            $this->related = $this->postRepository->getRelated((int) $post->getId(), $limit);
        } catch (\Exception $e) {
            $this->related = [];
        }
        return $this->related;
    }

    /**
     * @param \ThirdParty\BlogArticle\Api\Data\PostInterface|Post $post
     * @return string
     */
    public function getPostUrl($post): string
    {
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/' . $urlKey]);
        }
        return $this->getUrl('blog/post/view', ['id' => (int) $post->getPostId() ?: (int) $post->getId()]);
    }

    /**
     * @return string[]
     */
    public function getTagNames(): array
    {
        $names = [];
        foreach ($this->getTags() as $tag) {
            $names[] = $tag['name'];
        }
        return $names;
    }

    /**
     * Active tags with name + url for linking.
     *
     * @return array<int, array{name:string,url:string,url_key:string}>
     */
    public function getTags(): array
    {
        $post = $this->getPost();
        if (!$post) {
            return [];
        }
        $tags = [];
        foreach ($this->postTagLink->getTagIdsForPost((int) $post->getId()) as $tagId) {
            if (!array_key_exists($tagId, $this->tagMetaCache)) {
                $tag = $this->tagFactory->create()->load($tagId);
                if ($tag->getId() && (int) $tag->getIsActive()) {
                    $urlKey = (string) $tag->getUrlKey();
                    $this->tagMetaCache[$tagId] = [
                        'name' => (string) $tag->getName(),
                        'url_key' => $urlKey,
                        'url' => $urlKey !== ''
                            ? $this->getUrl('', ['_direct' => 'blog/tag/' . $urlKey])
                            : $this->getUrl('blog/index/index', ['tag_id' => $tagId]),
                    ];
                } else {
                    $this->tagMetaCache[$tagId] = null;
                }
            }
            if ($this->tagMetaCache[$tagId] !== null) {
                $tags[] = $this->tagMetaCache[$tagId];
            }
        }
        return $tags;
    }

    public function getCategoryName(): string
    {
        $post = $this->getPost();
        if (!$post || !(int) $post->getCategoryId()) {
            return '';
        }
        $category = $this->categoryFactory->create()->load((int) $post->getCategoryId());
        if (!$category->getId() || !(int) $category->getIsActive()) {
            return '';
        }
        return (string) $category->getName();
    }

    public function getCategoryUrl(): string
    {
        $post = $this->getPost();
        if (!$post || !(int) $post->getCategoryId()) {
            return '';
        }
        $category = $this->categoryFactory->create()->load((int) $post->getCategoryId());
        if (!$category->getId() || !(int) $category->getIsActive()) {
            return '';
        }
        $urlKey = (string) $category->getUrlKey();
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/category/' . $urlKey]);
        }
        return $this->getUrl('blog/index/index', ['category_id' => (int) $category->getId()]);
    }

    /**
     * Estimated reading time in minutes (min 1).
     */
    public function getReadingMinutes(): int
    {
        $post = $this->getPost();
        if (!$post) {
            return 1;
        }
        $text = trim(strip_tags((string) $post->getContent()));
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $count = is_array($words) ? count($words) : 0;
        return max(1, (int) ceil($count / 200));
    }

    /**
     * Share links (X/Twitter, Facebook, LinkedIn, mailto).
     *
     * @return array<string, string>
     */
    public function getShareLinks(): array
    {
        $url = rawurlencode($this->getCanonicalUrl());
        $title = rawurlencode($this->getPageTitle());
        return [
            'twitter' => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url,
            'email' => 'mailto:?subject=' . $title . '&body=' . $url,
        ];
    }

    /**
     * Previous / next public posts by publish sort (newer = next).
     *
     * @return array{prev:?Post,next:?Post}
     */
    public function getNeighborPosts(): array
    {
        if ($this->neighbors !== null) {
            return $this->neighbors;
        }
        $this->neighbors = ['prev' => null, 'next' => null];
        $post = $this->getPost();
        if (!$post) {
            return $this->neighbors;
        }

        $collection = $this->postCollectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applyPublishedOnly($collection);
        $this->postFilter->applyStoreId($collection, (int) $this->storeManager->getStore()->getId());
        $this->postFilter->applyDefaultSort($collection);

        $ids = [];
        foreach ($collection as $item) {
            $ids[] = (int) $item->getId();
        }
        $currentId = (int) $post->getId();
        $index = array_search($currentId, $ids, true);
        if ($index === false) {
            return $this->neighbors;
        }
        // Default sort is newest first: index-1 is newer (next), index+1 is older (prev)
        if (isset($ids[$index - 1])) {
            $this->neighbors['next'] = $this->postFactory->create()->load($ids[$index - 1]);
        }
        if (isset($ids[$index + 1])) {
            $this->neighbors['prev'] = $this->postFactory->create()->load($ids[$index + 1]);
        }
        return $this->neighbors;
    }

    public function getPreviousPost(): ?Post
    {
        return $this->getNeighborPosts()['prev'];
    }

    public function getNextPost(): ?Post
    {
        return $this->getNeighborPosts()['next'];
    }

    public function getListUrl(): string
    {
        return $this->getUrl('blog/index/index');
    }

    public function getCanonicalUrl(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/' . $urlKey]);
        }
        return $this->getUrl('blog/post/view', ['id' => (int) $post->getId()]);
    }

    /**
     * @return array<int, array{hreflang:string,href:string}>
     */
    public function getHreflangLinks(): array
    {
        $post = $this->getPost();
        if (!$post) {
            return [];
        }
        $urlKey = trim((string) $post->getUrlKey());
        $path = $urlKey !== '' ? 'blog/' . $urlKey : 'blog/post/view/id/' . (int) $post->getId();
        $storeId = $post->getStoreId() ? (int) $post->getStoreId() : null;
        return $this->hreflangBuilder->buildForPath($path, $storeId);
    }

    public function isReadingMode(): bool
    {
        return (bool) $this->getRequest()->getParam('reading');
    }

    public function isReadingModeLinkEnabled(): bool
    {
        return $this->config->isReadingModeLinkEnabled();
    }

    public function getReadingModeUrl(): string
    {
        $url = $this->getCanonicalUrl();
        if ($url === '') {
            return '';
        }
        $sep = strpos($url, '?') === false ? '?' : '&';
        return $url . $sep . 'reading=1';
    }

    public function getStandardModeUrl(): string
    {
        return $this->getCanonicalUrl();
    }

    public function getPageTitle(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return (string) __('Blog post');
        }
        $meta = trim((string) $post->getMetaTitle());
        return $meta !== '' ? $meta : (string) $post->getTitle();
    }

    public function getMetaDescription(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        return trim((string) $post->getMetaDescription());
    }

    public function getAllowedContentTags(): array
    {
        return [
            'p', 'br', 'em', 'strong', 'b', 'i', 'ul', 'ol', 'li', 'a',
            'h2', 'h3', 'h4', 'blockquote', 'code', 'pre', 'img', 'figure', 'figcaption',
        ];
    }

    public function isTocEnabled(): bool
    {
        return $this->config->isTocEnabled();
    }

    /**
     * Table of contents built from h2–h4 in the post body (empty when disabled or below threshold).
     *
     * @return array<int, array{id:string,level:int,text:string}>
     */
    public function getTocItems(): array
    {
        $this->getPreparedContentHtml();
        if (!$this->isTocEnabled() || !is_array($this->tocItems)) {
            return [];
        }
        $min = $this->config->getTocMinHeadings();
        if (count($this->tocItems) < $min) {
            return [];
        }
        return $this->tocItems;
    }

    /**
     * Escaped post HTML with heading IDs (for TOC anchors) and optional lazy-load on &lt;img&gt;.
     */
    public function getPreparedContentHtml(): string
    {
        if ($this->preparedContentHtml !== null) {
            return $this->preparedContentHtml;
        }

        $post = $this->getPost();
        if (!$post) {
            $this->preparedContentHtml = '';
            $this->tocItems = [];
            return '';
        }

        $html = $this->escapeHtml((string) $post->getContent(), $this->getAllowedContentTags());
        $this->tocItems = [];

        if ($html !== '') {
            $html = $this->injectHeadingIdsAndBuildToc($html);
        }

        if ($this->isLazyLoadImagesEnabled() && $html !== '') {
            $html = (string) preg_replace_callback(
                '/<img\b([^>]*?)>/i',
                static function (array $matches): string {
                    $attrs = $matches[1];
                    if (preg_match('/\bloading\s*=/i', $attrs)) {
                        return '<img' . $attrs . '>';
                    }
                    $extra = ' loading="lazy" decoding="async"';
                    return '<img' . $extra . $attrs . '>';
                },
                $html
            );
        }

        $this->preparedContentHtml = $html;
        return $html;
    }

    /**
     * Add stable id attributes to h2–h4 and collect TOC entries.
     */
    private function injectHeadingIdsAndBuildToc(string $html): string
    {
        $used = [];
        $items = [];
        $result = (string) preg_replace_callback(
            '/<(h([2-4]))(\b[^>]*)>(.*?)<\/\1>/is',
            function (array $m) use (&$used, &$items): string {
                $tag = strtolower($m[1]);
                $level = (int) $m[2];
                $attrs = $m[3];
                $inner = $m[4];
                $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($text === '') {
                    return $m[0];
                }

                $id = '';
                if (preg_match('/\bid\s*=\s*(["\'])([^"\']+)\1/i', $attrs, $idMatch)) {
                    $id = $this->sanitizeHeadingId($idMatch[2]);
                }
                if ($id === '') {
                    $id = $this->slugifyHeading($text);
                }
                if ($id === '') {
                    $id = 'section';
                }
                $base = $id;
                $n = 2;
                while (isset($used[$id])) {
                    $id = $base . '-' . $n;
                    $n++;
                }
                $used[$id] = true;

                $items[] = [
                    'id' => $id,
                    'level' => $level,
                    'text' => $text,
                ];

                // Strip existing id attribute then inject sanitized one.
                $attrs = preg_replace('/\s*\bid\s*=\s*(["\'])[^"\']*\1/i', '', $attrs) ?? $attrs;
                return '<' . $tag . $attrs . ' id="' . $this->escapeHtmlAttr($id) . '">' . $inner . '</' . $tag . '>';
            },
            $html
        );

        $this->tocItems = $items;
        return $result !== '' ? $result : $html;
    }

    private function sanitizeHeadingId(string $id): string
    {
        $id = strtolower(trim($id));
        $id = preg_replace('/[^a-z0-9\-_]+/', '-', $id) ?? '';
        $id = trim($id, '-_');
        return $id;
    }

    private function slugifyHeading(string $text): string
    {
        $text = strtolower($text);
        if (function_exists('iconv')) {
            $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($trans) && $trans !== '') {
                $text = $trans;
            }
        }
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        return trim($text, '-');
    }

    public function getFeaturedImageUrl(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        return $this->imageUploader->resolveUrl(
            $post->getFeaturedImage() ? (string) $post->getFeaturedImage() : null
        );
    }

    public function isCommentsEnabled(): bool
    {
        return $this->config->isCommentsEnabled();
    }

    /**
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface[]
     */
    public function getComments(): array
    {
        $post = $this->getPost();
        if (!$post || !$this->isCommentsEnabled()) {
            return [];
        }
        return $this->commentRepository->getListByPostId((int) $post->getId());
    }

    public function getCommentFormAction(): string
    {
        return $this->getUrl('blog/comment/post');
    }

    public function getFormKeyValue(): string
    {
        return $this->formKey->getFormKey();
    }

    public function getCommentFormTimestamp(): int
    {
        return (int) $this->dateTime->gmtTimestamp();
    }

    public function getCommentHoneypotField(): string
    {
        return CommentSpamGuard::HONEYPOT_FIELD;
    }

    public function getCommentTimestampField(): string
    {
        return CommentSpamGuard::TIMESTAMP_FIELD;
    }

    public function isSpamProtectionEnabled(): bool
    {
        return $this->config->isCommentSpamProtectionEnabled();
    }

    public function isRecaptchaEnabled(): bool
    {
        return $this->config->isRecaptchaEnabled() && $this->config->getRecaptchaSiteKey() !== '';
    }

    public function getRecaptchaSiteKey(): string
    {
        return $this->config->getRecaptchaSiteKey();
    }

    public function getAuthorName(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        return trim((string) $post->getAuthor());
    }

    public function getAuthorUrl(): string
    {
        $name = $this->getAuthorName();
        if ($name === '') {
            return '';
        }
        $slug = strtolower(preg_replace('/[\s_]+/', '-', $name) ?? $name);
        $slug = trim((string) $slug, '-');
        if ($slug === '') {
            return '';
        }
        return $this->getUrl('', ['_direct' => 'blog/author/' . rawurlencode($slug)]);
    }

    /**
     * Linked catalog products for this post (name + URL).
     *
     * @return array<int, array{name:string,url:string,sku:string}>
     */
    public function getRelatedProducts(): array
    {
        if ($this->relatedProducts !== null) {
            return $this->relatedProducts;
        }
        $this->relatedProducts = [];
        $post = $this->getPost();
        if (!$post || !$post->getId()) {
            return $this->relatedProducts;
        }
        foreach ($this->postProductLink->getProductIdsForPost((int) $post->getId()) as $productId) {
            try {
                $product = $this->productRepository->getById(
                    $productId,
                    false,
                    (int) $this->storeManager->getStore()->getId()
                );
                if (!(int) $product->getStatus()) {
                    continue;
                }
                $this->relatedProducts[] = [
                    'name' => (string) $product->getName(),
                    'url' => (string) $product->getProductUrl(),
                    'sku' => (string) $product->getSku(),
                ];
            } catch (\Throwable $e) {
                continue;
            }
        }
        return $this->relatedProducts;
    }

    /**
     * JSON-LD Article structured data for SEO.
     */
    public function getJsonLd(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        $description = trim((string) $post->getMetaDescription());
        if ($description === '') {
            $description = trim((string) $post->getExcerpt());
        }
        if ($description === '') {
            $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->getContent())) ?? '');
            if (function_exists('mb_substr')) {
                $description = mb_substr($description, 0, 300);
            } else {
                $description = substr($description, 0, 300);
            }
        }
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => (string) $post->getTitle(),
            'description' => $description,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->getCanonicalUrl(),
            ],
            'datePublished' => $post->getPublishedAt()
                ? (string) $post->getPublishedAt()
                : (string) $post->getCreationTime(),
            'dateModified' => (string) $post->getUpdateTime(),
        ];
        $author = $this->getAuthorName();
        if ($author !== '') {
            $data['author'] = [
                '@type' => 'Person',
                'name' => $author,
            ];
        }
        $image = $this->getFeaturedImageUrl();
        if ($image !== '') {
            $data['image'] = [$image];
        }

        $blogName = $this->config->getBlogName();
        $listUrl = $this->getListUrl();
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
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => (string) $post->getTitle(),
                    'item' => $this->getCanonicalUrl(),
                ],
            ],
        ];

        $graph = [
            '@context' => 'https://schema.org',
            '@graph' => [$data, $breadcrumb],
        ];
        return (string) json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Open Graph / Twitter meta pairs for the post head block.
     *
     * @return array<int, array{property?:string,name?:string,content:string}>
     */
    public function getSocialMetaTags(): array
    {
        $post = $this->getPost();
        if (!$post) {
            return [];
        }
        $title = $this->getPageTitle();
        $description = $this->getMetaDescription();
        if ($description === '') {
            $description = trim((string) $post->getExcerpt());
        }
        if ($description === '') {
            $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->getContent())) ?? '');
            if (function_exists('mb_substr')) {
                $description = mb_substr($description, 0, 200);
            } else {
                $description = substr($description, 0, 200);
            }
        }
        $url = $this->getCanonicalUrl();
        $image = $this->getFeaturedImageUrl();
        $tags = [
            ['property' => 'og:type', 'content' => 'article'],
            ['property' => 'og:title', 'content' => $title],
            ['property' => 'og:description', 'content' => $description],
            ['property' => 'og:url', 'content' => $url],
            ['name' => 'twitter:card', 'content' => $image !== '' ? 'summary_large_image' : 'summary'],
            ['name' => 'twitter:title', 'content' => $title],
            ['name' => 'twitter:description', 'content' => $description],
        ];
        if ($image !== '') {
            $tags[] = ['property' => 'og:image', 'content' => $image];
            $tags[] = ['name' => 'twitter:image', 'content' => $image];
        }
        $author = $this->getAuthorName();
        if ($author !== '') {
            $tags[] = ['property' => 'article:author', 'content' => $author];
        }
        return $tags;
    }

    public function getRootComments(): array
    {
        $roots = [];
        foreach ($this->getComments() as $comment) {
            if (!$comment->getParentId()) {
                $roots[] = $comment;
            }
        }
        return $roots;
    }

    /**
     * Direct replies to a parent comment (single nesting level).
     *
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface[]
     */
    public function getReplyComments(int $parentId): array
    {
        if ($parentId <= 0) {
            return [];
        }
        $replies = [];
        foreach ($this->getComments() as $comment) {
            if ((int) $comment->getParentId() === $parentId) {
                $replies[] = $comment;
            }
        }
        return $replies;
    }

    /**
     * Width/height for featured image when file is local (CLS-friendly).
     *
     * @return array{width:?int,height:?int}
     */
    public function getFeaturedImageDimensions(): array
    {
        $post = $this->getPost();
        if (!$post) {
            return ['width' => null, 'height' => null];
        }
        $value = $post->getFeaturedImage() ? (string) $post->getFeaturedImage() : null;
        return $this->imageUploader->getImageDimensions($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $post = $this->getPost();
        if (!$post) {
            return [Post::CACHE_TAG];
        }
        $tags = $post->getIdentities();
        // Related posts affect this block output.
        try {
            foreach ($this->getRelatedPosts() as $related) {
                if (method_exists($related, 'getIdentities')) {
                    $tags = array_merge($tags, $related->getIdentities());
                } elseif (method_exists($related, 'getPostId') && $related->getPostId()) {
                    $tags[] = Post::CACHE_TAG . '_' . (int) $related->getPostId();
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return array_values(array_unique($tags));
    }
}
