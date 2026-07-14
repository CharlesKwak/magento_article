<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Adds featured_image and SEO meta columns to posts.
 */
class AddFeaturedImageAndMeta implements SchemaPatchInterface
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

        if ($connection->isTableExists($table)) {
            $columns = [
                'featured_image' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 512,
                    'nullable' => true,
                    'comment' => 'Featured image URL or media path',
                    'after' => 'content',
                ],
                'meta_title' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Meta title',
                    'after' => 'featured_image',
                ],
                'meta_description' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 512,
                    'nullable' => true,
                    'comment' => 'Meta description',
                    'after' => 'meta_title',
                ],
            ];
            foreach ($columns as $name => $definition) {
                if (!$connection->tableColumnExists($table, $name)) {
                    $connection->addColumn($table, $name, $definition);
                }
            }
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
