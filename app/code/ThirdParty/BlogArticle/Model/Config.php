<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_PAGE_SIZE = 'blogarticle/list/page_size';
    public const XML_PATH_COMMENTS_ENABLED = 'blogarticle/comments/enabled';
    public const XML_PATH_COMMENTS_AUTO_APPROVE = 'blogarticle/comments/auto_approve';

    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getPageSize(?int $storeId = null): int
    {
        $size = (int) $this->scopeConfig->getValue(
            self::XML_PATH_PAGE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($size < 1) {
            return 5;
        }
        return min(50, $size);
    }

    public function isCommentsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENTS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCommentsAutoApprove(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENTS_AUTO_APPROVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
