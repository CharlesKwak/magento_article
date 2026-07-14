<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $categoryTableName = $installer->getTable('thirdparty_blogarticle_category');
        if (!$connection->isTableExists($categoryTableName)) {
            $categoryTable = $connection->newTable($categoryTableName)
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
                    $installer->getIdxName(
                        $categoryTableName,
                        ['url_key'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('BlogArticle Categories');
            $connection->createTable($categoryTable);
        }

        $postTableName = $installer->getTable('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($postTableName)) {
            $postTable = $connection->newTable($postTableName)
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Post ID'
                )->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Title'
                )->addColumn(
                    'author',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Post author display name'
                )->addColumn(
                    'url_key',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'URL Key'
                )->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => false],
                    'Content'
                )->addColumn(
                    'excerpt',
                    Table::TYPE_TEXT,
                    1024,
                    ['nullable' => true],
                    'Manual excerpt for list summaries'
                )->addColumn(
                    'featured_image',
                    Table::TYPE_TEXT,
                    512,
                    ['nullable' => true],
                    'Featured image URL or media path'
                )->addColumn(
                    'meta_title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Meta title'
                )->addColumn(
                    'meta_description',
                    Table::TYPE_TEXT,
                    512,
                    ['nullable' => true],
                    'Meta description'
                )->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => 1],
                    'Is Active'
                )->addColumn(
                    'category_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Category ID'
                )->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Store ID (NULL = all stores)'
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
                )->addColumn(
                    'published_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'Publish date/time for sorting'
                )->addIndex(
                    $installer->getIdxName(
                        $postTableName,
                        ['url_key'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName($postTableName, ['category_id']),
                    ['category_id']
                )->addIndex(
                    $installer->getIdxName($postTableName, ['store_id']),
                    ['store_id']
                )->setComment('BlogArticle Posts');
            $connection->createTable($postTable);
        }

        $tagTableName = $installer->getTable('thirdparty_blogarticle_tag');
        if (!$connection->isTableExists($tagTableName)) {
            $tagTable = $connection->newTable($tagTableName)
                ->addColumn(
                    'tag_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Tag ID'
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
                    $installer->getIdxName(
                        $tagTableName,
                        ['url_key'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['url_key'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('BlogArticle Tags');
            $connection->createTable($tagTable);
        }

        $linkTableName = $installer->getTable('thirdparty_blogarticle_post_tag');
        if (!$connection->isTableExists($linkTableName)) {
            $linkTable = $connection->newTable($linkTableName)
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Post ID'
                )->addColumn(
                    'tag_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Tag ID'
                )->addIndex(
                    $installer->getIdxName($linkTableName, ['tag_id']),
                    ['tag_id']
                )->setComment('BlogArticle Post-Tag Link');
            $connection->createTable($linkTable);
        }

        $commentTableName = $installer->getTable('thirdparty_blogarticle_comment');
        if (!$connection->isTableExists($commentTableName)) {
            $commentTable = $connection->newTable($commentTableName)
                ->addColumn('comment_id', Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Comment ID')
                ->addColumn('post_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Post ID')
                ->addColumn('author_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Author name')
                ->addColumn('author_email', Table::TYPE_TEXT, 255, ['nullable' => true], 'Author email')
                ->addColumn('content', Table::TYPE_TEXT, '64k', ['nullable' => false], 'Comment body')
                ->addColumn('is_approved', Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => 0], 'Is approved')
                ->addColumn('creation_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Creation Time')
                ->addIndex($installer->getIdxName($commentTableName, ['post_id']), ['post_id'])
                ->addIndex($installer->getIdxName($commentTableName, ['is_approved']), ['is_approved'])
                ->setComment('BlogArticle Comments');
            $connection->createTable($commentTable);
        }

        $installer->endSetup();
    }
}
