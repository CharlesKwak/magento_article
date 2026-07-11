<?php
namespace ThirdParty\BlogArticle\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $categoryTableName = $installer->getTable('thirdparty_blogarticle_category');
        if (!$connection->isTableExists($categoryTableName)) {
            $categoryTable = $connection->newTable($categoryTableName)
                ->addColumn(
                    'category_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Category ID'
                )->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Name'
                )->addColumn(
                    'url_key',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'URL Key'
                )->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => 1],
                    'Is Active'
                )->addColumn(
                    'creation_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Creation Time'
                )->addColumn(
                    'update_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                )->addIndex(
                    $installer->getIdxName(
                        $categoryTableName,
                        ['url_key'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('BlogArticle Categories');
            $connection->createTable($categoryTable);
        }

        $postTableName = $installer->getTable('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($postTableName)) {
            $postTable = $connection->newTable($postTableName)
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Post ID'
                )->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Title'
                )->addColumn(
                    'url_key',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'URL Key'
                )->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => false],
                    'Content'
                )->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => 1],
                    'Is Active'
                )->addColumn(
                    'category_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Category ID'
                )->addColumn(
                    'creation_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Creation Time'
                )->addColumn(
                    'update_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                )->addIndex(
                    $installer->getIdxName(
                        $postTableName,
                        ['url_key'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName($postTableName, ['category_id']),
                    ['category_id']
                )->setComment('BlogArticle Posts');
            $connection->createTable($postTable);
        }

        $installer->endSetup();
    }
}
