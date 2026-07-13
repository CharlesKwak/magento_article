<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Increments storefront view counters without loading the full post model.
 */
class PostViewCounter
{
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function increment(int $postId): void
    {
        if ($postId <= 0) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($table)
            || !$connection->tableColumnExists($table, 'view_count')
        ) {
            return;
        }
        $connection->query(
            sprintf(
                'UPDATE %s SET `view_count` = `view_count` + 1 WHERE `post_id` = ?',
                $table
            ),
            [$postId]
        );
    }
}
