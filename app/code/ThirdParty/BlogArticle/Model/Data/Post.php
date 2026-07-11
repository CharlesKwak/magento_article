<?php
namespace ThirdParty\BlogArticle\Model\Data;

use Magento\Framework\DataObject;
use ThirdParty\BlogArticle\Api\Data\PostInterface;

class Post extends DataObject implements PostInterface
{
    public function getPostId()
    {
        return $this->getData(self::POST_ID) !== null ? (int) $this->getData(self::POST_ID) : null;
    }

    public function setPostId($postId)
    {
        return $this->setData(self::POST_ID, $postId);
    }

    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE) !== null ? (int) $this->getData(self::IS_ACTIVE) : null;
    }

    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID) !== null && $this->getData(self::CATEGORY_ID) !== ''
            ? (int) $this->getData(self::CATEGORY_ID)
            : null;
    }

    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    public function getTagIds()
    {
        $ids = $this->getData(self::TAG_IDS);
        if ($ids === null) {
            return null;
        }
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_map('intval', $ids));
    }

    public function setTagIds(array $tagIds)
    {
        return $this->setData(self::TAG_IDS, array_values(array_map('intval', $tagIds)));
    }

    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }
}
