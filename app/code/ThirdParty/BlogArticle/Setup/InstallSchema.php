<?php
namespace ThirdParty\BlogArticle\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('thirdparty_blogarticle_post')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('thirdparty_blogarticle_post')
            )->addColumn(
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
                'content',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Content'
            )->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )->setComment('BlogArticle Posts');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
