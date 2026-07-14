<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Api\Data;

interface TagInterface
{
    public const TAG_ID = 'tag_id';
    public const NAME = 'name';
    public const URL_KEY = 'url_key';
    public const DESCRIPTION = 'description';
    public const IS_ACTIVE = 'is_active';
    public const CREATION_TIME = 'creation_time';
    public const UPDATE_TIME = 'update_time';

    /** @return int|null */
    public function getTagId();
    /**
     * @param int $tagId
     * @return $this
     */
    public function setTagId($tagId);
    /** @return string|null */
    public function getName();
    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);
    /** @return string|null */
    public function getUrlKey();
    /**
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);
    /** @return string|null */
    public function getDescription();
    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description);
    /** @return int|null */
    public function getIsActive();
    /**
     * @param int $isActive
     * @return $this
     */
    public function setIsActive($isActive);
    /** @return string|null */
    public function getCreationTime();
    /**
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime($creationTime);
    /** @return string|null */
    public function getUpdateTime();
    /**
     * @param string $updateTime
     * @return $this
     */
    public function setUpdateTime($updateTime);
}
