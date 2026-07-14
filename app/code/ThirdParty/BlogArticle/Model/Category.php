<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Model\AbstractModel;

class Category extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\ThirdParty\BlogArticle\Model\ResourceModel\Category::class);
    }
}
