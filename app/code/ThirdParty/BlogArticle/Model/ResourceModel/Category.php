<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Category extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('thirdparty_blogarticle_category', 'category_id');
    }
}
