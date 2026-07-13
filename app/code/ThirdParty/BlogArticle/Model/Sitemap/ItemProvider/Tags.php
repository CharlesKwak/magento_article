<?php
namespace ThirdParty\BlogArticle\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Adds active blog tag list URLs to Magento sitemap.
 */
class Tags implements ItemProviderInterface
{
    private $collectionFactory;
    private $itemFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->itemFactory = $itemFactory;
    }

    public function getItems($storeId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('name', 'ASC');

        $items = [];
        foreach ($collection as $tag) {
            $urlKey = trim((string) $tag->getUrlKey());
            if ($urlKey === '') {
                continue;
            }
            $updated = $tag->getUpdateTime() ?: $tag->getCreationTime();
            $items[] = $this->itemFactory->create([
                'url' => 'blog/tag/' . $urlKey,
                'updatedAt' => $updated,
                'priority' => 0.3,
                'changeFrequency' => 'weekly',
            ]);
        }
        return $items;
    }
}
