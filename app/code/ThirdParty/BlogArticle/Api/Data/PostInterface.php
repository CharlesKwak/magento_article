<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Api\Data;

/**
 * Blog post data interface for Web API.
 */
interface PostInterface
{
    public const POST_ID = 'post_id';
    public const TITLE = 'title';
    public const AUTHOR = 'author';
    public const URL_KEY = 'url_key';
    public const CONTENT = 'content';
    public const EXCERPT = 'excerpt';
    public const FEATURED_IMAGE = 'featured_image';
    public const META_TITLE = 'meta_title';
    public const META_DESCRIPTION = 'meta_description';
    public const META_ROBOTS = 'meta_robots';
    public const IS_ACTIVE = 'is_active';
    public const VIEW_COUNT = 'view_count';
    public const CATEGORY_ID = 'category_id';
    public const STORE_ID = 'store_id';
    public const TAG_IDS = 'tag_ids';
    public const CREATION_TIME = 'creation_time';
    public const UPDATE_TIME = 'update_time';
    public const PUBLISHED_AT = 'published_at';

    /** @return int|null */
    public function getPostId();
    /** @param int $postId @return $this */
    public function setPostId($postId);
    /** @return string|null */
    public function getTitle();
    /** @param string $title @return $this */
    public function setTitle($title);
    /** @return string|null */
    public function getAuthor();
    /** @param string|null $author @return $this */
    public function setAuthor($author);
    /** @return string|null */
    public function getUrlKey();
    /** @param string $urlKey @return $this */
    public function setUrlKey($urlKey);
    /** @return string|null */
    public function getContent();
    /** @param string $content @return $this */
    public function setContent($content);
    /** @return string|null */
    public function getExcerpt();
    /** @param string|null $excerpt @return $this */
    public function setExcerpt($excerpt);
    /** @return string|null */
    public function getFeaturedImage();
    /** @param string|null $featuredImage @return $this */
    public function setFeaturedImage($featuredImage);
    /** @return string|null */
    public function getMetaTitle();
    /** @param string|null $metaTitle @return $this */
    public function setMetaTitle($metaTitle);
    /** @return string|null */
    public function getMetaDescription();
    /** @param string|null $metaDescription @return $this */
    public function setMetaDescription($metaDescription);
    /** @return string|null */
    public function getMetaRobots();
    /** @param string|null $metaRobots @return $this */
    public function setMetaRobots($metaRobots);
    /** @return int|null */
    public function getIsActive();
    /** @param int $isActive @return $this */
    public function setIsActive($isActive);
    /** @return int|null */
    public function getViewCount();
    /** @param int $viewCount @return $this */
    public function setViewCount($viewCount);
    /** @return int|null */
    public function getCategoryId();
    /** @param int|null $categoryId @return $this */
    public function setCategoryId($categoryId);
    /** @return int|null */
    public function getStoreId();
    /** @param int|null $storeId @return $this */
    public function setStoreId($storeId);
    /** @return int[]|null */
    public function getTagIds();
    /** @param int[] $tagIds @return $this */
    public function setTagIds(array $tagIds);
    /** @return string|null */
    public function getCreationTime();
    /** @param string $creationTime @return $this */
    public function setCreationTime($creationTime);
    /** @return string|null */
    public function getUpdateTime();
    /** @param string $updateTime @return $this */
    public function setUpdateTime($updateTime);
    /** @return string|null */
    public function getPublishedAt();
    /** @param string|null $publishedAt @return $this */
    public function setPublishedAt($publishedAt);
}

