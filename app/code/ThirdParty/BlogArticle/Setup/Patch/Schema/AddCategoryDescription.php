<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Optional long text description for blog categories (storefront list intro).
 */
class AddCategoryDescription implements SchemaPatchInterface
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
        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_category');

        if ($connection->isTableExists($table)
            && !$connection->tableColumnExists($table, 'description')
        ) {
            $connection->addColumn(
                $table,
                'description',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'comment' => 'Category description (plain/HTML)',
                    'after' => 'url_key',
                ]
            );
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddCategorySupport::class];
    }

    public function getAliases()
    {
        return [];
    }
}
