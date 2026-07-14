<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Manages post ↔ tag many-to-many links.
 */
class PostTagLink
{
    public const TABLE = 'thirdparty_blogarticle_post_tag';

    private ResourceConnection $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param int $postId
     * @return int[]
     */
    public function getTagIdsForPost(int $postId): array
    {
        if ($postId <= 0) {
            return [];
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::TABLE);
        if (!$connection->isTableExists($table)) {
            return [];
        }
        $ids = $connection->fetchCol(
            $connection->select()
                ->from($table, ['tag_id'])
                ->where('post_id = ?', $postId)
        );
        return array_map('intval', $ids);
    }

    /**
     * Replace all tag links for a post.
     *
     * @param int $postId
     * @param int[] $tagIds
     * @return void
     */
    public function setTagsForPost(int $postId, array $tagIds): void
    {
        if ($postId <= 0) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::TABLE);
        if (!$connection->isTableExists($table)) {
            return;
        }

        $connection->delete($table, ['post_id = ?' => $postId]);

        $tagIds = array_values(array_unique(array_filter(array_map('intval', $tagIds))));
        if (!$tagIds) {
            return;
        }

        $rows = [];
        foreach ($tagIds as $tagId) {
            if ($tagId > 0) {
                $rows[] = ['post_id' => $postId, 'tag_id' => $tagId];
            }
        }
        if ($rows) {
            $connection->insertMultiple($table, $rows);
        }
    }

    /**
     * @param int $tagId
     * @return int
     */
    public function countPostsForTag(int $tagId): int
    {
        if ($tagId <= 0) {
            return 0;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(self::TABLE);
        if (!$connection->isTableExists($table)) {
            return 0;
        }
        return (int) $connection->fetchOne(
            $connection->select()
                ->from($table, [new \Magento\Framework\DB\Sql\Expression('COUNT(*)')])
                ->where('tag_id = ?', $tagId)
        );
    }
}
