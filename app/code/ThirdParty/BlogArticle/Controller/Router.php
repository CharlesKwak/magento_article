<?php
namespace ThirdParty\BlogArticle\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use ThirdParty\BlogArticle\Model\PostFactory;

/**
 * Maps /blog/{url_key} to blog/post/view.
 */
class Router implements RouterInterface
{
    /**
     * Reserved first path segments under /blog that are not URL keys.
     */
    private const RESERVED = [
        'index',
        'post',
        'robots.txt',
    ];

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var PostFactory
     */
    private $postFactory;

    public function __construct(
        ActionFactory $actionFactory,
        PostFactory $postFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->postFactory = $postFactory;
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

        // /blog or /blog/ → list (let standard router handle; no need to intercept)
        if ($identifier === 'blog') {
            return null;
        }

        if (!preg_match('#^blog/([^/]+)/?$#', $identifier, $matches)) {
            return null;
        }

        $urlKey = rawurldecode($matches[1]);
        if ($urlKey === '' || in_array(strtolower($urlKey), self::RESERVED, true)) {
            return null;
        }

        // Ignore query-like or multi-segment already handled above
        $post = $this->postFactory->create()->load($urlKey, 'url_key');
        if (!$post->getId() || !(int) $post->getIsActive()) {
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
