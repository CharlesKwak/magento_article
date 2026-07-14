<?php

declare(strict_types=1);

use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostVisibility;
use ThirdParty\BlogArticle\Model\PreviewToken;

class PostVisibilityTest extends TestCase
{
    private const PAST = '2000-01-01 00:00:00';
    private const FUTURE = '2999-01-01 00:00:00';

    private function makeVisibility(bool $tokenValid = false, int $currentStoreId = 1): PostVisibility
    {
        $previewToken = new class($tokenValid) extends PreviewToken {
            public function __construct(private bool $valid)
            {
            }

            public function isValid(string $token, int $postId): bool
            {
                return $this->valid;
            }
        };
        $storeManager = new class($currentStoreId) implements StoreManagerInterface {
            public function __construct(private int $storeId)
            {
            }

            public function getStore()
            {
                return new class($this->storeId) {
                    public function __construct(private int $id)
                    {
                    }

                    public function getId(): int
                    {
                        return $this->id;
                    }
                };
            }
        };
        return new PostVisibility($previewToken, $storeManager);
    }

    private function makePost(array $data): Post
    {
        return new Post($data);
    }

    public function testActivePublishedPostIsVisible(): void
    {
        $visibility = $this->makeVisibility();
        $post = $this->makePost(['id' => 1, 'is_active' => 1, 'published_at' => self::PAST, 'store_id' => 0]);
        $this->assertTrue($visibility->isVisible($post));
    }

    public function testPostWithoutIdIsNotVisible(): void
    {
        $visibility = $this->makeVisibility(true);
        $post = $this->makePost(['is_active' => 1]);
        $this->assertFalse($visibility->isVisible($post, true));
    }

    public function testInactivePostIsHiddenWithoutPreview(): void
    {
        $visibility = $this->makeVisibility();
        $post = $this->makePost(['id' => 1, 'is_active' => 0, 'store_id' => 0]);
        $this->assertFalse($visibility->isVisible($post));
    }

    public function testInactivePostIsVisibleWithPreview(): void
    {
        $visibility = $this->makeVisibility();
        $post = $this->makePost(['id' => 1, 'is_active' => 0, 'store_id' => 0]);
        $this->assertTrue($visibility->isVisible($post, true));
    }

    public function testScheduledPostIsHiddenUntilPublished(): void
    {
        $visibility = $this->makeVisibility();
        $post = $this->makePost(['id' => 1, 'is_active' => 1, 'published_at' => self::FUTURE, 'store_id' => 0]);
        $this->assertFalse($visibility->isVisible($post));
        $this->assertTrue($visibility->isVisible($post, true));
    }

    public function testEmptyPublishedAtCountsAsPublished(): void
    {
        $visibility = $this->makeVisibility();
        $post = $this->makePost(['id' => 1, 'is_active' => 1, 'store_id' => 0]);
        $this->assertTrue($visibility->isVisible($post));
    }

    public function testStoreScopeIsEnforcedEvenForPreviews(): void
    {
        $visibility = $this->makeVisibility(true, 1);
        $post = $this->makePost(['id' => 1, 'is_active' => 1, 'published_at' => self::PAST, 'store_id' => 2]);
        $this->assertFalse($visibility->isVisible($post));
        $this->assertFalse($visibility->isVisible($post, true), 'A preview token must not bypass the store scope.');
        $this->assertTrue($visibility->isVisible($post, false, 2));
    }

    public function testGlobalPostIsVisibleOnAnyStore(): void
    {
        $visibility = $this->makeVisibility(false, 3);
        $post = $this->makePost(['id' => 1, 'is_active' => 1, 'store_id' => 0]);
        $this->assertTrue($visibility->isVisible($post));
        $this->assertTrue($visibility->isVisible($post, false, 7));
    }

    public function testCurrentStoreIsUsedWhenStoreIdOmitted(): void
    {
        $visibility = $this->makeVisibility(false, 2);
        $post = $this->makePost(['id' => 1, 'is_active' => 1, 'store_id' => 2]);
        $this->assertTrue($visibility->isVisible($post));
        $this->assertFalse($visibility->isVisible($post, false, 1));
    }

    public function testIsPreviewAllowedRequiresTokenAndPostId(): void
    {
        $visibility = $this->makeVisibility(true);
        $post = $this->makePost(['id' => 5]);
        $this->assertTrue($visibility->isPreviewAllowed($post, 'token'));
        $this->assertFalse($visibility->isPreviewAllowed($post, ''), 'Empty token must never allow preview.');
        $this->assertFalse($visibility->isPreviewAllowed($this->makePost([]), 'token'));

        $invalidToken = $this->makeVisibility(false);
        $this->assertFalse($invalidToken->isPreviewAllowed($post, 'token'));
    }

    public function testIsActiveAndPublished(): void
    {
        $visibility = $this->makeVisibility();
        $this->assertTrue($visibility->isActiveAndPublished(
            $this->makePost(['id' => 1, 'is_active' => 1, 'published_at' => self::PAST])
        ));
        $this->assertFalse($visibility->isActiveAndPublished(
            $this->makePost(['id' => 1, 'is_active' => 1, 'published_at' => self::FUTURE])
        ));
        $this->assertFalse($visibility->isActiveAndPublished(
            $this->makePost(['id' => 1, 'is_active' => 0])
        ));
        $this->assertTrue($visibility->isActiveAndPublished(
            $this->makePost(['id' => 1, 'is_active' => 1])
        ));
    }
}
