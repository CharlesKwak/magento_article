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
            ['title', 'content', 'url_key', 'excerpt', 'author', 'meta_title'],
            [
                ['like' => $like],
                ['like' => $like],
                ['like' => $like],
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
        $this->applySort($collection, 'newest');
    }

    /**
     * Apply list sort mode: newest|oldest|title_asc|title_desc|most_viewed.
     *
     * @param Collection $collection
     * @param string|null $sort
     * @return void
     */
    public function applySort(Collection $collection, ?string $sort): void
    {
        $sort = strtolower(trim((string) $sort));
        $allowed = Source\ListSort::allowed();
        if ($sort === '' || !in_array($sort, $allowed, true)) {
            $sort = Source\ListSort::NEWEST;
        }

        $dateDesc = new \Magento\Framework\DB\Sql\Expression(
            'IFNULL(main_table.published_at, main_table.creation_time) DESC'
        );
        $dateAsc = new \Magento\Framework\DB\Sql\Expression(
            'IFNULL(main_table.published_at, main_table.creation_time) ASC'
        );

        switch ($sort) {
            case Source\ListSort::OLDEST:
                $collection->getSelect()->order($dateAsc);
                break;
            case Source\ListSort::TITLE_ASC:
                $collection->setOrder('title', 'ASC');
                break;
            case Source\ListSort::TITLE_DESC:
                $collection->setOrder('title', 'DESC');
                break;
            case Source\ListSort::MOST_VIEWED:
                $collection->getSelect()->order(
                    new \Magento\Framework\DB\Sql\Expression(
                        'IFNULL(main_table.view_count, 0) DESC'
                    )
                );
                $collection->getSelect()->order($dateDesc);
                break;
            case Source\ListSort::NEWEST:
            default:
                $collection->getSelect()->order($dateDesc);
                break;
        }
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

    /**
     * Filter by author display name or URL slug (spaces/underscores ↔ hyphens, case-insensitive).
     *
     * @param Collection $collection
     * @param string|null $authorKey
     * @return void
     */
    public function applyAuthorKey(Collection $collection, ?string $authorKey): void
    {
        $authorKey = trim((string) $authorKey);
        if ($authorKey === '') {
            return;
        }
        $normalized = strtolower(preg_replace('/[\s_]+/', '-', $authorKey) ?? $authorKey);
        $normalized = trim($normalized, '-');
        if ($normalized === '') {
            return;
        }
        $collection->getSelect()->where(
            "LOWER(REPLACE(REPLACE(TRIM(IFNULL(main_table.author, '')), ' ', '-'), '_', '-')) = ?",
            $normalized
        );
    }

    /**
     * Filter by calendar year and optional month of publish/create time.
     *
     * @param Collection $collection
     * @param int|null $year
     * @param int|null $month 1–12 or null for full year
     * @return void
     */
    public function applyYearMonth(Collection $collection, ?int $year, ?int $month = null): void
    {
        if ($year === null || $year < 1970 || $year > 2100) {
            return;
        }
        $dateExpr = 'IFNULL(main_table.published_at, main_table.creation_time)';
        if ($month !== null && $month >= 1 && $month <= 12) {
            $start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            try {
                $end = (new \DateTimeImmutable($start))->modify('first day of next month')->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return;
            }
            $collection->getSelect()->where($dateExpr . ' >= ?', $start);
            $collection->getSelect()->where($dateExpr . ' < ?', $end);
            return;
        }
        $start = sprintf('%04d-01-01 00:00:00', $year);
        $end = sprintf('%04d-01-01 00:00:00', $year + 1);
        $collection->getSelect()->where($dateExpr . ' >= ?', $start);
        $collection->getSelect()->where($dateExpr . ' < ?', $end);
    }
}
