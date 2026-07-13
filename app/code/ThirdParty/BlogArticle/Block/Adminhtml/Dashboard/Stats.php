<?php
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
    private $postCollectionFactory;
    private $commentCollectionFactory;
    private $resource;

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

    public function getPostsUrl(): string
    {
        return $this->getUrl('blogarticle/post/index');
    }

    public function getCommentsUrl(): string
    {
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
