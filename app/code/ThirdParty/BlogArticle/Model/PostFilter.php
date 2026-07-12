<?php
namespace ThirdParty\BlogArticle\Model;

use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;

/**
 * Shared collection filters for storefront, Admin, REST, and GraphQL.
 */
class PostFilter
{
    /**
     * @param Collection $collection
     * @param string|null $search
     * @return void
     */
    public function applySearch(Collection $collection, ?string $search): void
    {
        $search = trim((string) $search);
        if ($search === '') {
            return;
        }

        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
        $like = '%' . $escaped . '%';

        $collection->addFieldToFilter(
            ['title', 'content', 'url_key'],
            [
                ['like' => $like],
                ['like' => $like],
                ['like' => $like],
            ]
        );
    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function applyActiveOnly(Collection $collection): void
    {
        $collection->addFieldToFilter('is_active', 1);
    }

    /**
     * Hide posts scheduled for a future publish time.
     * NULL published_at is treated as immediately available.
     *
     * @param Collection $collection
     * @param string|null $nowGmt Y-m-d H:i:s in GMT
     * @return void
     */
    public function applyPublishedOnly(Collection $collection, ?string $nowGmt = null): void
    {
        if ($nowGmt === null) {
            $nowGmt = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        }
        $collection->getSelect()->where(
            '(main_table.published_at IS NULL OR main_table.published_at <= ?)',
            $nowGmt
        );
    }

    /**
     * @param Collection $collection
     * @param int|null $categoryId
     * @return void
     */
    public function applyCategoryId(Collection $collection, ?int $categoryId): void
    {
        if ($categoryId === null || $categoryId <= 0) {
            return;
        }
        $collection->addFieldToFilter('category_id', $categoryId);
    }

    /**
     * Filter posts that have the given tag (join link table).
     *
     * @param Collection $collection
     * @param int|null $tagId
     * @return void
     */
    public function applyTagId(Collection $collection, ?int $tagId): void
    {
        if ($tagId === null || $tagId <= 0) {
            return;
        }
        $linkTable = $collection->getTable('thirdparty_blogarticle_post_tag');
        $collection->getSelect()->join(
            ['blog_pt' => $linkTable],
            'main_table.post_id = blog_pt.post_id',
            []
        )->where('blog_pt.tag_id = ?', $tagId)
            ->group('main_table.post_id');
    }

    /**
     * Sort by published_at (fallback creation_time), newest first.
     *
     * @param Collection $collection
     * @return void
     */
    public function applyDefaultSort(Collection $collection): void
    {
        $collection->getSelect()->order(
            new \Magento\Framework\DB\Sql\Expression(
                'IFNULL(main_table.published_at, main_table.creation_time) DESC'
            )
        );
    }

    /**
     * Posts for all stores (NULL/0) or the given store view.
     *
     * @param Collection $collection
     * @param int|null $storeId
     * @return void
     */
    public function applyStoreId(Collection $collection, ?int $storeId): void
    {
        if ($storeId === null || $storeId <= 0) {
            return;
        }
        $collection->getSelect()->where(
            '(main_table.store_id IS NULL OR main_table.store_id = 0 OR main_table.store_id = ?)',
            $storeId
        );
    }
}
