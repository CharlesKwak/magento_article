<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

/**
 * Adds active blog posts to Magento sitemap generation.
 */
class Posts implements ItemProviderInterface
{
    private CollectionFactory $collectionFactory;
    private PostFilter $postFilter;
    private SitemapItemInterfaceFactory $itemFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        PostFilter $postFilter,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->postFilter = $postFilter;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applyPublishedOnly($collection);
        $this->postFilter->applyStoreId($collection, (int) $storeId);
        $this->postFilter->applyDefaultSort($collection);

        $items = [];
        foreach ($collection as $post) {
            $urlKey = (string) $post->getUrlKey();
            if ($urlKey === '') {
                continue;
            }
            $updated = $post->getUpdateTime() ?: $post->getPublishedAt() ?: $post->getCreationTime();
            $items[] = $this->itemFactory->create([
                'url' => 'blog/' . $urlKey,
                'updatedAt' => $updated,
                'priority' => 0.5,
                'changeFrequency' => 'weekly',
            ]);
        }
        return $items;
    }
}
