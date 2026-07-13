<?php
namespace ThirdParty\BlogArticle\Observer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
use ThirdParty\BlogArticle\Model\Comment;
use ThirdParty\BlogArticle\Model\Post;

/**
 * Invalidate blog block + full-page cache tags when posts or comments change.
 * Ignores all other models (model_save_after is global).
 */
class FlushBlogCache implements ObserverInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var PageCacheType
     */
    private $pageCache;

    public function __construct(
        CacheInterface $cache,
        PageCacheType $pageCache
    ) {
        $this->cache = $cache;
        $this->pageCache = $pageCache;
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

        // Default cache frontend (block HTML etc.)
        $this->cache->clean($tags);
        // Full Page Cache (built-in FPC) — tag-based purge
        $this->pageCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
    }
}
