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
    public const IS_ACTIVE = 'is_active';
    public const CREATION_TIME = 'creation_time';
    public const UPDATE_TIME = 'update_time';

    /**
     * @return int|null
     */
    public function getPostId();

    /**
     * @param int $postId
     * @return $this
     */
    public function setPostId($postId);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getUrlKey();

    /**
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * @return string|null
     */
    public function getContent();

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return int|null
     */
    public function getIsActive();

    /**
     * @param int $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return string|null
     */
    public function getCreationTime();

    /**
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime($creationTime);

    /**
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * @param string $updateTime
     * @return $this
     */
    public function setUpdateTime($updateTime);
}
