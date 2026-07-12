<?php
namespace ThirdParty\BlogArticle\Model\ResourceModel\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \ThirdParty\BlogArticle\Model\Comment::class,
            \ThirdParty\BlogArticle\Model\ResourceModel\Comment::class
        );
    }
}
