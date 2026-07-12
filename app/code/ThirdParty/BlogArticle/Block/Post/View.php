<?php
namespace ThirdParty\BlogArticle\Block\Post;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Stdlib\DateTime\DateTime;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\CommentSpamGuard;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\TagFactory;

class View extends Template implements IdentityInterface
{
    private $postFactory;
    private $postRepository;
    private $postTagLink;
    private $tagFactory;
    private $imageUploader;
    private $commentRepository;
    private $config;
    private $formKey;
    private $dateTime;
    private $post;
    private $related;
    private $tagNameCache = [];

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        PostRepositoryInterface $postRepository,
        PostTagLink $postTagLink,
        TagFactory $tagFactory,
        FeaturedImageUploader $imageUploader,
        CommentRepositoryInterface $commentRepository,
        Config $config,
        FormKey $formKey,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->postRepository = $postRepository;
        $this->postTagLink = $postTagLink;
        $this->tagFactory = $tagFactory;
        $this->imageUploader = $imageUploader;
        $this->commentRepository = $commentRepository;
        $this->config = $config;
        $this->formKey = $formKey;
        $this->dateTime = $dateTime;
        parent::__construct($context, $data);
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
        $post = $this->getPost();
        if (!$post) {
            return [];
        }
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

    /**
     * Top-level approved comments (no parent).
     *
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface[]
     */
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
