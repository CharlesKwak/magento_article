<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Seo;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\Config;

/**
 * Builds hreflang alternate link pairs for multi-store blog URLs.
 */
class HreflangBuilder
{
    private StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;
    private Config $config;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * @param string $path Relative path after base URL, e.g. "blog/" or "blog/my-post"
     * @param int|null $postStoreId NULL/0 = all stores; otherwise only that store + x-default
     * @return array<int, array{hreflang:string,href:string}>
     */
    public function buildForPath(string $path, ?int $postStoreId = null): array
    {
        if (!$this->config->isHreflangEnabled()) {
            return [];
        }

        $path = ltrim($path, '/');
        $links = [];
        $currentStoreId = (int) $this->storeManager->getStore()->getId();
        $xDefault = null;

        foreach ($this->storeManager->getStores(false) as $store) {
            if (!(int) $store->getIsActive()) {
                continue;
            }
            $storeId = (int) $store->getId();
            if ($postStoreId !== null && $postStoreId > 0 && $storeId !== $postStoreId) {
                continue;
            }
            $locale = (string) $this->scopeConfig->getValue(
                'general/locale/code',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($locale === '') {
                continue;
            }
            $hreflang = str_replace('_', '-', $locale);
            $base = rtrim($store->getBaseUrl(), '/');
            $href = $base . '/' . $path;
            $links[] = [
                'hreflang' => $hreflang,
                'href' => $href,
            ];
            if ($xDefault === null || $storeId === $currentStoreId) {
                $xDefault = $href;
            }
        }

        if ($xDefault !== null && $links) {
            $links[] = [
                'hreflang' => 'x-default',
                'href' => $xDefault,
            ];
        }

        return $links;
    }
}
