<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Block;

use Magento\Framework\View\Element\Html\Link as HtmlLink;
use Magento\Framework\View\Element\Template\Context;
use ThirdParty\BlogArticle\Model\Config;

/**
 * Storefront footer / toplink pointing at the blog list.
 */
class Link extends HtmlLink
{
    private Config $config;

    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    public function getHref()
    {
        return $this->getUrl('blog/index/index');
    }

    public function getLabel()
    {
        return $this->config->getBlogName();
    }

    protected function _toHtml()
    {
        if (!$this->config->isShowFooterLink()) {
            return '';
        }
        return parent::_toHtml();
    }
}
