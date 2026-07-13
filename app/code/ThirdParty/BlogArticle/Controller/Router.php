<?php
namespace ThirdParty\BlogArticle\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\TagFactory;

/**
 * Maps clean blog URLs:
 *  - /blog/{url_key} → post view
 *  - /blog/category/{url_key} → list filtered by category
 *  - /blog/tag/{url_key} → list filtered by tag
 */
class Router implements RouterInterface
{
    /**
     * Reserved first path segments under /blog that are not post URL keys.
     */
    private const RESERVED = [
        'index',
        'post',
        'category',
        'tag',
        'author',
        'archive',
        'comment',
        'rss',
        'robots.txt',
    ];

    private $actionFactory;
    private $postFactory;
    private $categoryFactory;
    private $tagFactory;
    private $storeManager;

    public function __construct(
        ActionFactory $actionFactory,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim((string) $request->getPathInfo(), '/');
        if ($identifier === '' || strpos($identifier, 'blog') !== 0) {
            return null;
        }

        // /blog or /blog/ → list (standard router)
        if ($identifier === 'blog') {
            return null;
        }

        // /blog/category/{url_key}
        if (preg_match('#^blog/category/([^/]+)/?$#', $identifier, $matches)) {
            $urlKey = rawurldecode($matches[1]);
            if ($urlKey === '') {
                return null;
            }
            $category = $this->categoryFactory->create()->load($urlKey, 'url_key');
            if (!$category->getId() || !(int) $category->getIsActive()) {
                return null;
            }
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('cat', $urlKey)
                ->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            return $this->actionFactory->create(Forward::class);
        }

        // /blog/tag/{url_key}
        if (preg_match('#^blog/tag/([^/]+)/?$#', $identifier, $matches)) {
            $urlKey = rawurldecode($matches[1]);
            if ($urlKey === '') {
                return null;
            }
            $tag = $this->tagFactory->create()->load($urlKey, 'url_key');
            if (!$tag->getId() || !(int) $tag->getIsActive()) {
                return null;
            }
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('tag', $urlKey)
                ->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            return $this->actionFactory->create(Forward::class);
        }

        // /blog/author/{slug}  (slug ≈ lowercase author with spaces as hyphens)
        if (preg_match('#^blog/author/([^/]+)/?$#', $identifier, $matches)) {
            $authorKey = rawurldecode($matches[1]);
            if ($authorKey === '') {
                return null;
            }
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('author', $authorKey)
                ->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            return $this->actionFactory->create(Forward::class);
        }

        // /blog/archive/{YYYY} or /blog/archive/{YYYY}/{MM}
        if (preg_match('#^blog/archive/(\d{4})(?:/(\d{1,2}))?/?$#', $identifier, $matches)) {
            $year = (int) $matches[1];
            $month = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : 0;
            if ($year < 1970 || $year > 2100) {
                return null;
            }
            if ($month !== 0 && ($month < 1 || $month > 12)) {
                return null;
            }
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('year', $year);
            if ($month > 0) {
                $request->setParam('month', $month);
            }
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            return $this->actionFactory->create(Forward::class);
        }

        // /blog/{post_url_key}
        if (!preg_match('#^blog/([^/]+)/?$#', $identifier, $matches)) {
            return null;
        }

        $urlKey = rawurldecode($matches[1]);
        if ($urlKey === '' || in_array(strtolower($urlKey), self::RESERVED, true)) {
            return null;
        }

        $post = $this->postFactory->create()->load($urlKey, 'url_key');
        if (!$post->getId() || !(int) $post->getIsActive()) {
            return null;
        }
        $publishedAt = $post->getPublishedAt();
        if ($publishedAt) {
            $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();
            $pub = strtotime((string) $publishedAt . ' UTC');
            if ($pub && $pub > $now) {
                return null;
            }
        }
        $postStore = (int) $post->getStoreId();
        $currentStore = (int) $this->storeManager->getStore()->getId();
        if ($postStore > 0 && $postStore !== $currentStore) {
            return null;
        }

        $request->setModuleName('blog')
            ->setControllerName('post')
            ->setActionName('view')
            ->setParam('url_key', $urlKey)
            ->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

        return $this->actionFactory->create(Forward::class);
    }
}
