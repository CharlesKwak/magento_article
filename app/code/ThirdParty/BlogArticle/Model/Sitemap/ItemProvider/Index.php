<?php
namespace ThirdParty\BlogArticle\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

/**
 * Adds the main /blog/ index URL to Magento sitemap.
 */
class Index implements ItemProviderInterface
{
    private $itemFactory;

    public function __construct(SitemapItemInterfaceFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    public function getItems($storeId)
    {
        return [
            $this->itemFactory->create([
                'url' => 'blog/',
                'updatedAt' => date('Y-m-d H:i:s'),
                'priority' => 0.6,
                'changeFrequency' => 'daily',
            ]),
        ];
    }
}
