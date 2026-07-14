<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Adds optional store_id for multi-store visibility (NULL = all stores).
 */
class AddStoreId implements SchemaPatchInterface
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

        if ($connection->isTableExists($table) && !$connection->tableColumnExists($table, 'store_id')) {
            $connection->addColumn(
                $table,
                'store_id',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Store ID (NULL = all stores)',
                    'after' => 'category_id',
                ]
            );
            $connection->addIndex(
                $table,
                $this->moduleDataSetup->getIdxName($table, ['store_id']),
                ['store_id']
            );
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddExcerptAndPublishedAt::class];
    }

    public function getAliases()
    {
        return [];
    }
}
