<?php
namespace ThirdParty\BlogArticle\Model\ResourceModel\Post;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \ThirdParty\BlogArticle\Model\Post::class,
            \ThirdParty\BlogArticle\Model\ResourceModel\Post::class
        );
    }
}
