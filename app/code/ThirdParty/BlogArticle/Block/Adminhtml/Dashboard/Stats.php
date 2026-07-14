<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Block\Adminhtml\Dashboard;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ResourceConnection;
use ThirdParty\BlogArticle\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

/**
 * Compact blog metrics on Magento Admin dashboard.
 */
class Stats extends Template
{
    private PostCollectionFactory $postCollectionFactory;
    private CommentCollectionFactory $commentCollectionFactory;
    private ResourceConnection $resource;

    public function __construct(
        Context $context,
        PostCollectionFactory $postCollectionFactory,
        CommentCollectionFactory $commentCollectionFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->resource = $resource;
        parent::__construct($context, $data);
    }

    public function getPostsTotal(): int
    {
        return (int) $this->postCollectionFactory->create()->getSize();
    }

    public function getPostsEnabled(): int
    {
        $c = $this->postCollectionFactory->create();
        $c->addFieldToFilter('is_active', 1);
        return (int) $c->getSize();
    }

    public function getCommentsPending(): int
    {
        $c = $this->commentCollectionFactory->create();
        $c->addFieldToFilter('is_approved', 0);
        return (int) $c->getSize();
    }

    public function getViewsTotal(): int
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post');
        if (!$connection->isTableExists($table)
            || !$connection->tableColumnExists($table, 'view_count')
        ) {
            return 0;
        }
        $select = $connection->select()->from(
            $table,
            ['total' => new \Magento\Framework\DB\Sql\Expression('SUM(view_count)')]
        );
        return (int) $connection->fetchOne($select);
    }

    /**
     * Latest posts for dashboard snapshot.
     *
     * @return array<int, array{id:int,title:string,status:string}>
     */
    public function getRecentPosts(int $limit = 5): array
    {
        $c = $this->postCollectionFactory->create();
        $c->setOrder('update_time', 'DESC');
        $c->setPageSize(max(1, min(10, $limit)));
        $rows = [];
        foreach ($c as $post) {
            $rows[] = [
                'id' => (int) $post->getId(),
                'title' => (string) $post->getTitle(),
                'status' => (int) $post->getIsActive() ? 'enabled' : 'disabled',
            ];
        }
        return $rows;
    }

    /**
     * Top posts by view_count.
     *
     * @return array<int, array{id:int,title:string,views:int}>
     */
    public function getTopViewedPosts(int $limit = 5): array
    {
        $c = $this->postCollectionFactory->create();
        $c->setOrder('view_count', 'DESC');
        $c->setPageSize(max(1, min(10, $limit)));
        $rows = [];
        foreach ($c as $post) {
            $rows[] = [
                'id' => (int) $post->getId(),
                'title' => (string) $post->getTitle(),
                'views' => (int) $post->getData('view_count'),
            ];
        }
        return $rows;
    }

    public function getPostEditUrl(int $postId): string
    {
        return $this->getUrl('blogarticle/post/edit', ['post_id' => $postId]);
    }

    public function getPostsUrl(): string
    {
        return $this->getUrl('blogarticle/post/index');
    }

    public function getCommentsUrl(): string
    {
        return $this->getUrl('blogarticle/comment/index');
    }

    public function getPendingCommentsUrl(): string
    {
        // Grid filters are UI-state; deep-link to comments index.
        return $this->getUrl('blogarticle/comment/index');
    }

    public function getConfigUrl(): string
    {
        return $this->getUrl('adminhtml/system_config/edit', ['section' => 'blogarticle']);
    }

    protected function _toHtml()
    {
        if (!$this->_authorization->isAllowed('ThirdParty_BlogArticle::posts')) {
            return '';
        }
        return parent::_toHtml();
    }
}
