<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Cron;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use ThirdParty\BlogArticle\Model\Post;

/**
 * Clears blog cache tags when scheduled posts become visible.
 */
class FlushScheduledPostCache
{
    private ResourceConnection $resource;
    private CacheInterface $cache;
    private DateTime $dateTime;

    public function __construct(
        ResourceConnection $resource,
        CacheInterface $cache,
        DateTime $dateTime
    ) {
        $this->resource = $resource;
        $this->cache = $cache;
        $this->dateTime = $dateTime;
    }

    public function execute()
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($table) || !$connection->tableColumnExists($table, 'published_at')) {
            return;
        }

        $now = $this->dateTime->gmtDate();
        // Posts that became due in the last 10 minutes
        $from = $this->dateTime->gmtDate(null, strtotime('-10 minutes'));
        $select = $connection->select()
            ->from($table, ['post_id'])
            ->where('is_active = ?', 1)
            ->where('published_at IS NOT NULL')
            ->where('published_at > ?', $from)
            ->where('published_at <= ?', $now);

        $ids = $connection->fetchCol($select);
        if (!$ids) {
            return;
        }

        $tags = [Post::CACHE_TAG];
        foreach ($ids as $id) {
            $tags[] = Post::CACHE_TAG . '_' . (int) $id;
        }
        $this->cache->clean($tags);
    }
}
