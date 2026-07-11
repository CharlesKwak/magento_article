<?php
namespace ThirdParty\BlogArticle\Setup\Patch\Data;

use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use ThirdParty\BlogArticle\Setup\Patch\Schema\AddTagSupport;

/**
 * Seeds sample tags and links them to existing sample posts when empty.
 */
class AddSampleTags implements DataPatchInterface
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

        $tagTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_tag');
        $linkTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post_tag');
        $postTable = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');

        if (!$connection->isTableExists($tagTable)) {
            $connection->endSetup();
            return $this;
        }

        $count = (int) $connection->fetchOne(
            $connection->select()->from($tagTable, [new Expression('COUNT(*)')])
        );

        if ($count === 0) {
            $connection->insertMultiple(
                $tagTable,
                [
                    ['name' => 'News', 'url_key' => 'news', 'is_active' => 1],
                    ['name' => 'Guide', 'url_key' => 'guide', 'is_active' => 1],
                ]
            );
        }

        if ($connection->isTableExists($linkTable)
            && $connection->isTableExists($postTable)
        ) {
            $linkCount = (int) $connection->fetchOne(
                $connection->select()->from($linkTable, [new Expression('COUNT(*)')])
            );
            if ($linkCount === 0) {
                $newsId = (int) $connection->fetchOne(
                    $connection->select()->from($tagTable, ['tag_id'])->where('url_key = ?', 'news')
                );
                $guideId = (int) $connection->fetchOne(
                    $connection->select()->from($tagTable, ['tag_id'])->where('url_key = ?', 'guide')
                );
                $postIds = $connection->fetchCol(
                    $connection->select()->from($postTable, ['post_id'])->order('post_id ASC')->limit(2)
                );
                $rows = [];
                if (!empty($postIds[0]) && $newsId) {
                    $rows[] = ['post_id' => (int) $postIds[0], 'tag_id' => $newsId];
                }
                if (!empty($postIds[0]) && $guideId) {
                    $rows[] = ['post_id' => (int) $postIds[0], 'tag_id' => $guideId];
                }
                if (!empty($postIds[1]) && $guideId) {
                    $rows[] = ['post_id' => (int) $postIds[1], 'tag_id' => $guideId];
                }
                if ($rows) {
                    $connection->insertMultiple($linkTable, $rows);
                }
            }
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
            AddTagSupport::class,
            AddSampleBlogPosts::class,
            AddDefaultCategory::class,
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
