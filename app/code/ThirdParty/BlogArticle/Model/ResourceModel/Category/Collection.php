<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \ThirdParty\BlogArticle\Model\Category::class,
            \ThirdParty\BlogArticle\Model\ResourceModel\Category::class
        );
    }
}
