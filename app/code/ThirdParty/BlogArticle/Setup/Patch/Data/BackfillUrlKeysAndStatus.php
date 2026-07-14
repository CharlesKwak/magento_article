<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use ThirdParty\BlogArticle\Setup\Patch\Schema\AddUrlKeyAndStatusColumns;

/**
 * Backfills url_key values for existing posts after schema upgrade.
 */
class BackfillUrlKeysAndStatus implements DataPatchInterface
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

        $table = $this->moduleDataSetup->getTable('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($table)
            || !$connection->tableColumnExists($table, 'url_key')
        ) {
            $connection->endSetup();
            return $this;
        }

        $rows = $connection->fetchAll(
            $connection->select()
                ->from($table, ['post_id', 'title', 'url_key'])
        );

        $usedKeys = [];
        foreach ($rows as $row) {
            if (!empty($row['url_key'])) {
                $usedKeys[$row['url_key']] = true;
            }
        }

        foreach ($rows as $row) {
            if (!empty($row['url_key'])) {
                continue;
            }

            $base = $this->slugify((string) $row['title']);
            if ($base === '') {
                $base = 'post-' . (int) $row['post_id'];
            }

            $candidate = $base;
            $suffix = 1;
            while (isset($usedKeys[$candidate])) {
                $candidate = $base . '-' . $suffix;
                $suffix++;
            }

            $usedKeys[$candidate] = true;
            $connection->update(
                $table,
                ['url_key' => $candidate],
                ['post_id = ?' => (int) $row['post_id']]
            );
        }

        if ($connection->tableColumnExists($table, 'is_active')) {
            $connection->update(
                $table,
                ['is_active' => 1],
                'is_active IS NULL'
            );
        }

        $connection->endSetup();
        return $this;
    }

    /**
     * @param string $value
     * @return string
     */
    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddUrlKeyAndStatusColumns::class,
            AddSampleBlogPosts::class,
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
