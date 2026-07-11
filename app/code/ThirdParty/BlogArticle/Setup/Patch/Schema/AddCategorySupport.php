<?php
namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Creates blog category table and post.category_id column.
 */
class AddCategorySupport implements SchemaPatchInterface
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

        $categoryTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_category');
        if (!$connection->isTableExists($categoryTable)) {
            $table = $connection->newTable($categoryTable)
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
                    $this->moduleDataSetup->getIdxName(
                        $categoryTable,
                        ['url_key'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('BlogArticle Categories');
            $connection->createTable($table);
        }

        $postTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');
        if ($connection->isTableExists($postTable)
            && !$connection->tableColumnExists($postTable, 'category_id')
        ) {
            $connection->addColumn(
                $postTable,
                'category_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Category ID',
                    'after' => 'is_active',
                ]
            );
            $connection->addIndex(
                $postTable,
                $this->moduleDataSetup->getIdxName($postTable, ['category_id']),
                ['category_id']
            );
        }

        $connection->endSetup();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddUrlKeyAndStatusColumns::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
