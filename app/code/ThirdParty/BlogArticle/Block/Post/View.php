<?php
namespace ThirdParty\BlogArticle\Block\Post;

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
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use ThirdParty\BlogArticle\Model\TagFactory;

class View extends Template implements IdentityInterface
{
    private $postFactory;
    private $postRepository;
    private $postTagLink;
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
    private $neighbors;
    private $tagNameCache = [];
    private $tagMetaCache = [];

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        PostRepositoryInterface $postRepository,
        PostTagLink $postTagLink,
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
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->postRepository = $postRepository;
        $this->postTagLink = $postTagLink;
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
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addBreadcrumbs();
        return $this;
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
            $this->related = $this->postRepository->getRelated((int) $post->getId(), 3);
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
        return ['p', 'br', 'em', 'strong', 'b', 'i', 'ul', 'ol', 'li', 'a', 'h2', 'h3', 'h4'];
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
        return (string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $post = $this->getPost();
        if (!$post) {
            return [Post::CACHE_TAG];
        }
        return $post->getIdentities();
    }
}
