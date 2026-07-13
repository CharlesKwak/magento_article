<?php
namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Adds post view_count and post↔product link table for related products / most viewed.
 */
class AddViewCountAndProductLink implements SchemaPatchInterface
{
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $postTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');
        if ($connection->isTableExists($postTable)
            && !$connection->tableColumnExists($postTable, 'view_count')
        ) {
            $connection->addColumn(
                $postTable,
                'view_count',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Storefront view counter',
                    'after' => 'is_active',
                ]
            );
        }

        $linkTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post_product');
        if (!$connection->isTableExists($linkTable)) {
            $table = $connection->newTable($linkTable)
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Post ID'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Product ID'
                )->addIndex(
                    $this->moduleDataSetup->getIdxName($linkTable, ['product_id']),
                    ['product_id']
                )->setComment('BlogArticle Post to Catalog Product');
            $connection->createTable($table);
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddAuthorColumn::class];
    }

    public function getAliases()
    {
        return [];
    }
}
