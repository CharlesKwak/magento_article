<?php
namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Adds url_key, is_active, and update_time columns for detail pages and publish control.
 */
class AddUrlKeyAndStatusColumns implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');
        if ($connection->isTableExists($table)) {
            if (!$connection->tableColumnExists($table, 'url_key')) {
                $connection->addColumn(
                    $table,
                    'url_key',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'URL Key',
                        'after' => 'title',
                    ]
                );
            }

            if (!$connection->tableColumnExists($table, 'is_active')) {
                $connection->addColumn(
                    $table,
                    'is_active',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => 1,
                        'comment' => 'Is Active',
                        'after' => 'content',
                    ]
                );
            }

            if (!$connection->tableColumnExists($table, 'update_time')) {
                $connection->addColumn(
                    $table,
                    'update_time',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT_UPDATE,
                        'comment' => 'Update Time',
                        'after' => 'creation_time',
                    ]
                );
            }

            $indexList = $connection->getIndexList($table);
            $hasUrlKeyIndex = false;
            foreach ($indexList as $index) {
                if (!empty($index['COLUMNS_LIST']) && $index['COLUMNS_LIST'] === ['url_key']) {
                    $hasUrlKeyIndex = true;
                    break;
                }
            }
            if (!$hasUrlKeyIndex) {
                $connection->addIndex(
                    $table,
                    $this->moduleDataSetup->getIdxName(
                        $table,
                        ['url_key'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );
            }
        }

        $connection->endSetup();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
