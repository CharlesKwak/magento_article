<?php
namespace ThirdParty\BlogArticle\Model\ResourceModel;

class Post extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('thirdparty_blogarticle_post', 'post_id');
    }
}
