<?php
namespace ThirdParty\BlogArticle\Api\Data;

/**
 * Blog post data interface for Web API.
 */
interface PostInterface
{
    public const POST_ID = 'post_id';
    public const TITLE = 'title';
    public const URL_KEY = 'url_key';
    public const CONTENT = 'content';
    public const FEATURED_IMAGE = 'featured_image';
    public const META_TITLE = 'meta_title';
    public const META_DESCRIPTION = 'meta_description';
    public const IS_ACTIVE = 'is_active';
    public const CATEGORY_ID = 'category_id';
    public const TAG_IDS = 'tag_ids';
    public const CREATION_TIME = 'creation_time';
    public const UPDATE_TIME = 'update_time';

    /** @return int|null */
    public function getPostId();
    /** @param int $postId @return $this */
    public function setPostId($postId);
    /** @return string|null */
    public function getTitle();
    /** @param string $title @return $this */
    public function setTitle($title);
    /** @return string|null */
    public function getUrlKey();
    /** @param string $urlKey @return $this */
    public function setUrlKey($urlKey);
    /** @return string|null */
    public function getContent();
    /** @param string $content @return $this */
    public function setContent($content);
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
    /** @return int|null */
    public function getIsActive();
    /** @param int $isActive @return $this */
    public function setIsActive($isActive);
    /** @return int|null */
    public function getCategoryId();
    /** @param int|null $categoryId @return $this */
    public function setCategoryId($categoryId);
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
}
