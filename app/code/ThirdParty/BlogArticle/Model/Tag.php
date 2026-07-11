<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Model\AbstractModel;

class Tag extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\ThirdParty\BlogArticle\Model\ResourceModel\Tag::class);
    }
}
