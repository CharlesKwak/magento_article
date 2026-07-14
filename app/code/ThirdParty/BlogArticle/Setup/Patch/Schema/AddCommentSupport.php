<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class AddCommentSupport implements SchemaPatchInterface
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
        $tableName = $this->moduleDataSetup->getTable('thirdparty_blogarticle_comment');

        if (!$connection->isTableExists($tableName)) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'comment_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Comment ID'
                )->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Post ID'
                )->addColumn(
                    'parent_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Parent comment ID for replies'
                )->addColumn(
                    'author_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Author name'
                )->addColumn(
                    'author_email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Author email'
                )->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => false],
                    'Comment body'
                )->addColumn(
                    'is_approved',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => 0],
                    'Is approved'
                )->addColumn(
                    'creation_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Creation Time'
                )->addIndex(
                    $this->moduleDataSetup->getIdxName($tableName, ['post_id']),
                    ['post_id']
                )->addIndex(
                    $this->moduleDataSetup->getIdxName($tableName, ['parent_id']),
                    ['parent_id']
                )->addIndex(
                    $this->moduleDataSetup->getIdxName($tableName, ['is_approved']),
                    ['is_approved']
                )->setComment('BlogArticle Comments');
            $connection->createTable($table);
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddStoreId::class];
    }

    public function getAliases()
    {
        return [];
    }
}
