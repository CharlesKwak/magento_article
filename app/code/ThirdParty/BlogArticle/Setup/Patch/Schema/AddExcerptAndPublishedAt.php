<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Adds excerpt and published_at columns for list summaries and sort order.
 */
class AddExcerptAndPublishedAt implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();
        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');

        if ($connection->isTableExists($table)) {
            if (!$connection->tableColumnExists($table, 'excerpt')) {
                $connection->addColumn(
                    $table,
                    'excerpt',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 1024,
                        'nullable' => true,
                        'comment' => 'Manual excerpt for list summaries',
                        'after' => 'content',
                    ]
                );
            }
            if (!$connection->tableColumnExists($table, 'published_at')) {
                $connection->addColumn(
                    $table,
                    'published_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'nullable' => true,
                        'comment' => 'Publish date/time for sorting',
                        'after' => 'update_time',
                    ]
                );
            }
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddFeaturedImageAndMeta::class];
    }

    public function getAliases()
    {
        return [];
    }
}
