<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Data;

use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use ThirdParty\BlogArticle\Setup\Patch\Schema\AddCategorySupport;

/**
 * Seeds a default "General" category and assigns existing posts without one.
 */
class AddDefaultCategory implements DataPatchInterface
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

        $categoryTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_category');
        $postTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');

        if (!$connection->isTableExists($categoryTable)) {
            $connection->endSetup();
            return $this;
        }

        $count = (int) $connection->fetchOne(
            $connection->select()->from($categoryTable, [new Expression('COUNT(*)')])
        );

        $categoryId = null;
        if ($count === 0) {
            $connection->insert(
                $categoryTable,
                [
                    'name' => 'General',
                    'url_key' => 'general',
                    'is_active' => 1,
                ]
            );
            $categoryId = (int) $connection->lastInsertId($categoryTable);
        } else {
            $categoryId = (int) $connection->fetchOne(
                $connection->select()
                    ->from($categoryTable, ['category_id'])
                    ->where('url_key = ?', 'general')
                    ->limit(1)
            );
            if (!$categoryId) {
                $categoryId = (int) $connection->fetchOne(
                    $connection->select()
                        ->from($categoryTable, ['category_id'])
                        ->order('category_id ASC')
                        ->limit(1)
                );
            }
        }

        if ($categoryId
            && $connection->isTableExists($postTable)
            && $connection->tableColumnExists($postTable, 'category_id')
        ) {
            $connection->update(
                $postTable,
                ['category_id' => $categoryId],
                'category_id IS NULL'
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
            AddCategorySupport::class,
            AddSampleBlogPosts::class,
            BackfillUrlKeysAndStatus::class,
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
