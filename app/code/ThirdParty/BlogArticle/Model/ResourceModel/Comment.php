<?php
namespace ThirdParty\BlogArticle\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Comment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('thirdparty_blogarticle_comment', 'comment_id');
    }
}
