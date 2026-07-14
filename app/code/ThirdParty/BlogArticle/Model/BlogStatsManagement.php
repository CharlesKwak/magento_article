<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\App\ResourceConnection;
use ThirdParty\BlogArticle\Api\BlogStatsManagementInterface;
use ThirdParty\BlogArticle\Api\Data\BlogStatsInterfaceFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

class BlogStatsManagement implements BlogStatsManagementInterface
{
    private BlogStatsInterfaceFactory $statsFactory;
    private PostCollectionFactory $postCollectionFactory;
    private CommentCollectionFactory $commentCollectionFactory;
    private CategoryCollectionFactory $categoryCollectionFactory;
    private TagCollectionFactory $tagCollectionFactory;
    private ResourceConnection $resource;

    public function __construct(
        BlogStatsInterfaceFactory $statsFactory,
        PostCollectionFactory $postCollectionFactory,
        CommentCollectionFactory $commentCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        ResourceConnection $resource
    ) {
        $this->statsFactory = $statsFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->resource = $resource;
    }

    public function get()
    {
        $enabled = $this->postCollectionFactory->create();
        $enabled->addFieldToFilter('is_active', 1);
        $pending = $this->commentCollectionFactory->create();
        $pending->addFieldToFilter('is_approved', 0);

        $viewsTotal = 0;
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post');
        if ($connection->isTableExists($table)
            && $connection->tableColumnExists($table, 'view_count')
        ) {
            $select = $connection->select()->from(
                $table,
                ['total' => new \Magento\Framework\DB\Sql\Expression('SUM(view_count)')]
            );
            $viewsTotal = (int) $connection->fetchOne($select);
        }

        /** @var \ThirdParty\BlogArticle\Api\Data\BlogStatsInterface $stats */
        $stats = $this->statsFactory->create();
        $stats->setPostsTotal((int) $this->postCollectionFactory->create()->getSize());
        $stats->setPostsEnabled((int) $enabled->getSize());
        $stats->setCommentsTotal((int) $this->commentCollectionFactory->create()->getSize());
        $stats->setCommentsPending((int) $pending->getSize());
        $stats->setCategoriesTotal((int) $this->categoryCollectionFactory->create()->getSize());
        $stats->setTagsTotal((int) $this->tagCollectionFactory->create()->getSize());
        $stats->setViewsTotal($viewsTotal);
        return $stats;
    }
}
