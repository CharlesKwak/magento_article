<?php
namespace ThirdParty\BlogArticle\Setup\Patch\Data;

use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Seeds sample blog posts when the table is empty (first install / upgrade).
 */
class AddSampleBlogPosts implements DataPatchInterface
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

        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($table)) {
            $connection->endSetup();
            return $this;
        }

        $count = (int) $connection->fetchOne(
            $connection->select()->from($table, [new Expression('COUNT(*)')])
        );

        if ($count === 0) {
            $connection->insertMultiple(
                $table,
                [
                    [
                        'title' => 'Welcome to the blog',
                        'content' => '<p>This is the first sample article installed for verification.</p>',
                    ],
                    [
                        'title' => 'Second sample post',
                        'content' => '<p>Use <strong>Content → Blog Posts</strong> in Admin to edit or add more posts.</p>',
                    ],
                ]
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
