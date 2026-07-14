<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Single source of truth for the storefront post visibility rule shared by
 * the router, controllers, blocks and repository: a post is visible when it
 * is active, its published_at (UTC) is not in the future and it belongs to
 * the current store (or all stores). A valid signed ?preview= token bypasses
 * the whole gate for draft/scheduled previews.
 */
class PostVisibility
{
    private PreviewToken $previewToken;
    private StoreManagerInterface $storeManager;

    public function __construct(
        PreviewToken $previewToken,
        StoreManagerInterface $storeManager
    ) {
        $this->previewToken = $previewToken;
        $this->storeManager = $storeManager;
    }

    /**
     * Full storefront gate. The store scope is enforced even for previews
     * (a preview token only bypasses the active/published checks). Compute
     * $allowPreview once via isPreviewAllowed(). Pass $storeId to reuse a
     * known store scope; defaults to the current store.
     */
    public function isVisible(Post $post, bool $allowPreview = false, ?int $storeId = null): bool
    {
        if (!$post->getId()) {
            return false;
        }
        if ($storeId === null) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }
        if (!$this->matchesStore($post, $storeId)) {
            return false;
        }
        return $allowPreview || $this->isActiveAndPublished($post);
    }

    public function isPreviewAllowed(Post $post, string $previewTokenValue): bool
    {
        return $previewTokenValue !== ''
            && $post->getId()
            && $this->previewToken->isValid($previewTokenValue, (int) $post->getId());
    }

    public function isActiveAndPublished(Post $post): bool
    {
        if (!(int) $post->getIsActive()) {
            return false;
        }
        return $this->isPublished($post);
    }

    /**
     * published_at empty or not in the future (compared in UTC).
     */
    private function isPublished(Post $post): bool
    {
        $publishedAt = $post->getPublishedAt();
        if (!$publishedAt) {
            return true;
        }
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();
        $publishedTs = strtotime((string) $publishedAt . ' UTC');
        return !$publishedTs || $publishedTs <= $now;
    }

    /**
     * Post is global (store_id 0/null) or assigned to the given store.
     */
    private function matchesStore(Post $post, int $storeId): bool
    {
        $postStore = (int) $post->getStoreId();
        return $postStore <= 0 || $storeId <= 0 || $postStore === $storeId;
    }
}
