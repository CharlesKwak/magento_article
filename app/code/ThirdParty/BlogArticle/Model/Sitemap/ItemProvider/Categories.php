<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory;

/**
 * Adds active blog category list URLs to Magento sitemap.
 */
class Categories implements ItemProviderInterface
{
    private CollectionFactory $collectionFactory;
    private SitemapItemInterfaceFactory $itemFactory;

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
        $collection->setOrder('sort_order', 'ASC');
        $collection->setOrder('name', 'ASC');

        $items = [];
        foreach ($collection as $category) {
            $urlKey = trim((string) $category->getUrlKey());
            if ($urlKey === '') {
                continue;
            }
            $updated = $category->getUpdateTime() ?: $category->getCreationTime();
            $items[] = $this->itemFactory->create([
                'url' => 'blog/category/' . $urlKey,
                'updatedAt' => $updated,
                'priority' => 0.4,
                'changeFrequency' => 'weekly',
            ]);
        }
        return $items;
    }
}
