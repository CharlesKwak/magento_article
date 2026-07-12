<?php
namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Adds parent_id for nested comment replies.
 */
class AddCommentParentId implements SchemaPatchInterface
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
        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_comment');

        if ($connection->isTableExists($table) && !$connection->tableColumnExists($table, 'parent_id')) {
            $connection->addColumn(
                $table,
                'parent_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Parent comment ID for replies',
                    'after' => 'post_id',
                ]
            );
            $connection->addIndex(
                $table,
                $this->moduleDataSetup->getIdxName($table, ['parent_id']),
                ['parent_id']
            );
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddCommentSupport::class];
    }

    public function getAliases()
    {
        return [];
    }
}
