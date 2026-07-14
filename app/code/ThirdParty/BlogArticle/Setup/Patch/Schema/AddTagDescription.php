<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Optional description text for blog tags (storefront tag list intro).
 */
class AddTagDescription implements SchemaPatchInterface
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
        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_tag');

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
                    'comment' => 'Tag description (plain/HTML)',
                    'after' => 'url_key',
                ]
            );
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddTagSupport::class];
    }

    public function getAliases()
    {
        return [];
    }
}
