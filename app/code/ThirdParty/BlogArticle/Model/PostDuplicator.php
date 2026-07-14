<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Clones a post (content + taxonomy links) as a disabled draft with a unique URL key.
 */
class PostDuplicator
{
    private PostFactory $postFactory;
    private UrlKeyGenerator $urlKeyGenerator;
    private PostTagLink $postTagLink;
    private PostProductLink $postProductLink;

    public function __construct(
        PostFactory $postFactory,
        UrlKeyGenerator $urlKeyGenerator,
        PostTagLink $postTagLink,
        PostProductLink $postProductLink
    ) {
        $this->postFactory = $postFactory;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->postTagLink = $postTagLink;
        $this->postProductLink = $postProductLink;
    }

    /**
     * @param int $postId Source post ID
     * @param bool $keepStatus When true, copy is_active; otherwise force disabled
     * @return Post Newly saved post model
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function duplicate(int $postId, bool $keepStatus = false): Post
    {
        $source = $this->postFactory->create()->load($postId);
        if (!$source->getId()) {
            throw new NoSuchEntityException(__('The blog post with ID "%1" does not exist.', $postId));
        }

        $title = (string) $source->getTitle();
        $copyTitle = (string) __('Copy of %1', $title);
        $urlSource = trim((string) $source->getUrlKey());
        if ($urlSource === '') {
            $urlSource = $copyTitle;
        } else {
            $urlSource .= '-copy';
        }
        $urlKey = $this->urlKeyGenerator->generate($urlSource, null);

        $copy = $this->postFactory->create();
        $copy->setTitle($copyTitle);
        $copy->setAuthor($source->getAuthor());
        $copy->setContent($source->getContent());
        $copy->setUrlKey($urlKey);
        $copy->setExcerpt($source->getExcerpt());
        $copy->setFeaturedImage($source->getFeaturedImage());
        $copy->setMetaTitle($source->getMetaTitle());
        $copy->setMetaDescription($source->getMetaDescription());
        $copy->setData('meta_robots', $source->getData('meta_robots'));
        $copy->setPublishedAt(null);
        $copy->setCategoryId($source->getCategoryId() ? (int) $source->getCategoryId() : null);
        $copy->setStoreId(
            $source->getStoreId() !== null && $source->getStoreId() !== ''
                ? (int) $source->getStoreId()
                : null
        );
        if ($keepStatus) {
            $copy->setIsActive((int) $source->getIsActive());
        } else {
            $copy->setIsActive(0);
        }
        $copy->setData('view_count', 0);

        try {
            $copy->save();
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not duplicate the post: %1', $e->getMessage()), $e);
        }

        $newId = (int) $copy->getId();
        $tagIds = $this->postTagLink->getTagIdsForPost((int) $source->getId());
        $this->postTagLink->setTagsForPost($newId, $tagIds);
        $productIds = $this->postProductLink->getProductIdsForPost((int) $source->getId());
        $this->postProductLink->replaceLinks($newId, $productIds);

        return $copy;
    }
}
