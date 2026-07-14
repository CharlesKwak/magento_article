<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use ThirdParty\BlogArticle\Model\Archive;

/**
 * Adds monthly archive list URLs to Magento sitemap.
 */
class Archives implements ItemProviderInterface
{
    private Archive $archive;
    private SitemapItemInterfaceFactory $itemFactory;

    public function __construct(
        Archive $archive,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->archive = $archive;
        $this->itemFactory = $itemFactory;
    }

    public function getItems($storeId)
    {
        $items = [];
        $buckets = $this->archive->getMonthlyBuckets(60, (int) $storeId);
        foreach ($buckets as $bucket) {
            $year = (int) $bucket['year'];
            $month = (int) $bucket['month'];
            if ($year < 1970 || $month < 1 || $month > 12) {
                continue;
            }
            $items[] = $this->itemFactory->create([
                'url' => sprintf('blog/archive/%04d/%02d', $year, $month),
                'updatedAt' => date('Y-m-d H:i:s'),
                'priority' => 0.3,
                'changeFrequency' => 'monthly',
            ]);
        }
        return $items;
    }
}
