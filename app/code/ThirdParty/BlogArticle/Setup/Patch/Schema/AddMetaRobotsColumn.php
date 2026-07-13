<?php
namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Optional robots directive per post (INDEX,FOLLOW / NOINDEX,NOFOLLOW / …).
 */
class AddMetaRobotsColumn implements SchemaPatchInterface
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
        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');

        if ($connection->isTableExists($table)
            && !$connection->tableColumnExists($table, 'meta_robots')
        ) {
            $connection->addColumn(
                $table,
                'meta_robots',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 32,
                    'nullable' => true,
                    'comment' => 'Robots meta (INDEX,FOLLOW etc). Empty = INDEX,FOLLOW',
                    'after' => 'meta_description',
                ]
            );
        }

        $connection->endSetup();
        return $this;
    }

    public static function getDependencies()
    {
        return [AddViewCountAndProductLink::class];
    }

    public function getAliases()
    {
        return [];
    }
}
