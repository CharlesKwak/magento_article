<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Creates tag and post-tag relation tables.
 */
class AddTagSupport implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

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

        $tagTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_tag');
        if (!$connection->isTableExists($tagTable)) {
            $table = $connection->newTable($tagTable)
                ->addColumn(
                    'tag_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Tag ID'
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
                    $this->moduleDataSetup->getIdxName(
                        $tagTable,
                        ['url_key'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('BlogArticle Tags');
            $connection->createTable($table);
        }

        $linkTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post_tag');
        if (!$connection->isTableExists($linkTable)) {
            $table = $connection->newTable($linkTable)
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Post ID'
                )->addColumn(
                    'tag_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Tag ID'
                )->addIndex(
                    $this->moduleDataSetup->getIdxName($linkTable, ['tag_id']),
                    ['tag_id']
                )->setComment('BlogArticle Post-Tag Link');
            $connection->createTable($table);
        }

        $connection->endSetup();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [AddCategorySupport::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
