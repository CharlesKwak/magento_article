<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Monthly post archive aggregates for storefront sidebar and filters.
 */
class Archive
{
    private $resource;
    private $storeManager;

    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array<int, array{year:int,month:int,count:int,label:string}>
     */
    public function getMonthlyBuckets(int $limit = 24, ?int $storeId = null): array
    {
        $limit = max(1, min(120, $limit));
        if ($storeId === null) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($table)) {
            return [];
        }

        $dateExpr = 'IFNULL(published_at, creation_time)';
        $nowGmt = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $select = $connection->select()
            ->from(
                $table,
                [
                    'year' => new Expression('YEAR(' . $dateExpr . ')'),
                    'month' => new Expression('MONTH(' . $dateExpr . ')'),
                    'post_count' => new Expression('COUNT(*)'),
                ]
            )
            ->where('is_active = ?', 1)
            ->where('(published_at IS NULL OR published_at <= ?)', $nowGmt);

        if ($storeId > 0) {
            $select->where('(store_id IS NULL OR store_id = 0 OR store_id = ?)', $storeId);
        }

        $select->group(['year', 'month'])
            ->order('year DESC')
            ->order('month DESC')
            ->limit($limit);

        $rows = $connection->fetchAll($select);
        $out = [];
        foreach ($rows as $row) {
            $year = (int) ($row['year'] ?? 0);
            $month = (int) ($row['month'] ?? 0);
            $count = (int) ($row['post_count'] ?? 0);
            if ($year < 1970 || $month < 1 || $month > 12 || $count < 1) {
                continue;
            }
            $label = $this->formatMonthLabel($year, $month);
            $out[] = [
                'year' => $year,
                'month' => $month,
                'count' => $count,
                'label' => $label,
            ];
        }
        return $out;
    }

    public function formatMonthLabel(int $year, int $month): string
    {
        try {
            $dt = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
            return $dt->format('F Y');
        } catch (\Exception $e) {
            return sprintf('%04d-%02d', $year, $month);
        }
    }
}
