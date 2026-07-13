<?php
namespace ThirdParty\BlogArticle\Observer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ThirdParty\BlogArticle\Model\Comment;
use ThirdParty\BlogArticle\Model\Post;

/**
 * Invalidate blog block cache tags when posts or comments change.
 * Ignores all other models (model_save_after is global).
 */
class FlushBlogCache implements ObserverInterface
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function execute(Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        if (!$object instanceof Post && !$object instanceof Comment) {
            return;
        }

        $tags = [Post::CACHE_TAG];
        if ($object instanceof Post && $object->getId()) {
            $tags[] = Post::CACHE_TAG . '_' . (int) $object->getId();
            if ($object->getCategoryId()) {
                $tags[] = 'blogarticle_category_' . (int) $object->getCategoryId();
            }
        }
        if ($object instanceof Comment && $object->getPostId()) {
            $tags[] = Post::CACHE_TAG . '_' . (int) $object->getPostId();
        }

        $this->cache->clean($tags);
    }
}
