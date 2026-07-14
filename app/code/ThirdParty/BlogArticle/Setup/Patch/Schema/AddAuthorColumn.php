<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Adds optional author display name on blog posts.
 */
class AddAuthorColumn implements SchemaPatchInterface
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
        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');

        if ($connection->isTableExists($table) && !$connection->tableColumnExists($table, 'author')) {
            $connection->addColumn(
                $table,
                'author',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Post author display name',
                    'after' => 'title',
                ]
            );
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddCommentParentId::class];
    }

    public function getAliases()
    {
        return [];
    }
}
