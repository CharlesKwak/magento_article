<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_PAGE_SIZE = 'blogarticle/list/page_size';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Storefront list page size.
     *
     * @param int|null $storeId
     * @return int
     */
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
}
