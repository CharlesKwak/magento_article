<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Topmenu as TopmenuBlock;
use ThirdParty\BlogArticle\Model\Config;

/**
 * Injects a Blog link into the storefront top navigation when enabled.
 */
class Topmenu
{
    private Config $config;
    private UrlInterface $urlBuilder;
    private NodeFactory $nodeFactory;
    private RequestInterface $request;

    public function __construct(
        Config $config,
        UrlInterface $urlBuilder,
        NodeFactory $nodeFactory,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->nodeFactory = $nodeFactory;
        $this->request = $request;
    }

    /**
     * @param TopmenuBlock $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetHtml(
        TopmenuBlock $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        if (!$this->config->isShowTopMenu()) {
            return [$outermostClass, $childrenWrapClass, $limit];
        }

        $menu = $subject->getMenu();
        if (!$menu) {
            return [$outermostClass, $childrenWrapClass, $limit];
        }
        // Avoid duplicate nodes if getHtml() is invoked more than once per request.
        if ($menu->getChild('thirdparty-blog-article')) {
            return [$outermostClass, $childrenWrapClass, $limit];
        }

        $node = $this->nodeFactory->create(
            [
                'data' => [
                    'name' => $this->config->getBlogName(),
                    'id' => 'thirdparty-blog-article',
                    'url' => $this->urlBuilder->getUrl('blog/index/index'),
                    'has_active' => false,
                    'is_active' => $this->isBlogRequest(),
                ],
                'idField' => 'id',
                'tree' => $menu->getTree(),
            ]
        );
        $menu->addChild($node);

        return [$outermostClass, $childrenWrapClass, $limit];
    }

    private function isBlogRequest(): bool
    {
        $module = (string) $this->request->getModuleName();
        if ($module === 'blog') {
            return true;
        }
        $path = trim((string) $this->request->getPathInfo(), '/');
        return $path === 'blog' || strpos($path, 'blog/') === 0;
    }
}
