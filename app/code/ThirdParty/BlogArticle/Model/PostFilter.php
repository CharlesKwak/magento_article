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
}
