<?php
namespace ThirdParty\BlogArticle\Model;

use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;

/**
 * Shared collection filters for storefront, Admin, REST, and GraphQL.
 */
class PostFilter
{
    /**
     * Apply free-text search across title, content, and url_key.
     *
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

        // Escape LIKE wildcards in user input
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
     * Restrict to enabled posts for public surfaces.
     *
     * @param Collection $collection
     * @return void
     */
    public function applyActiveOnly(Collection $collection): void
    {
        $collection->addFieldToFilter('is_active', 1);
    }
}
