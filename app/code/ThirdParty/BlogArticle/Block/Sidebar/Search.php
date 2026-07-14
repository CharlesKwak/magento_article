<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Block\Sidebar;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use ThirdParty\BlogArticle\Model\Config;

class Search extends Template
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

    public function isEnabled(): bool
    {
        return $this->config->isSidebarEnabled() && $this->config->isSidebarSearchEnabled();
    }

    public function getFormAction(): string
    {
        return $this->getUrl('blog/index/index');
    }

    public function getQuery(): string
    {
        return trim((string) $this->getRequest()->getParam('q', ''));
    }

    protected function _toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
