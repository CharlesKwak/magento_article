<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Model\GraphQl\Authorization;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

/**
 * Admin/integration GraphQL stats for dashboard-style UIs.
 */
class BlogStats implements ResolverInterface
{
    private $authorization;
    private $postCollectionFactory;
    private $commentCollectionFactory;
    private $categoryCollectionFactory;
    private $tagCollectionFactory;
    private $resource;

    public function __construct(
        Authorization $authorization,
        PostCollectionFactory $postCollectionFactory,
        CommentCollectionFactory $commentCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        ResourceConnection $resource
    ) {
        $this->authorization = $authorization;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->resource = $resource;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->authorization->assertCanWrite($context);

        $posts = $this->postCollectionFactory->create();
        $enabled = $this->postCollectionFactory->create();
        $enabled->addFieldToFilter('is_active', 1);

        $comments = $this->commentCollectionFactory->create();
        $pending = $this->commentCollectionFactory->create();
        $pending->addFieldToFilter('is_approved', 0);

        $totalViews = 0;
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post');
        if ($connection->isTableExists($table)
            && $connection->tableColumnExists($table, 'view_count')
        ) {
            $select = $connection->select()->from($table, ['total' => new \Magento\Framework\DB\Sql\Expression('SUM(view_count)')]);
            $totalViews = (int) $connection->fetchOne($select);
        }

        return [
            'posts_total' => (int) $posts->getSize(),
            'posts_enabled' => (int) $enabled->getSize(),
            'comments_total' => (int) $comments->getSize(),
            'comments_pending' => (int) $pending->getSize(),
            'categories_total' => (int) $this->categoryCollectionFactory->create()->getSize(),
            'tags_total' => (int) $this->tagCollectionFactory->create()->getSize(),
            'views_total' => $totalViews,
        ];
    }
}
