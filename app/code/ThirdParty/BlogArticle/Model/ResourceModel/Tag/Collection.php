<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\ResourceModel\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \ThirdParty\BlogArticle\Model\Tag::class,
            \ThirdParty\BlogArticle\Model\ResourceModel\Tag::class
        );
    }
}
