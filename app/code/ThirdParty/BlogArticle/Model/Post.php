<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Post extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'blogarticle_post';

    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init(\ThirdParty\BlogArticle\Model\ResourceModel\Post::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $tags = [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
        if ($this->getCategoryId()) {
            $tags[] = 'blogarticle_category_' . (int) $this->getCategoryId();
        }
        return $tags;
    }
}
