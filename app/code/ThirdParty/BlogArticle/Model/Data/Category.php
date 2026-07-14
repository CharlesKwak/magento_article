<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Data;

use Magento\Framework\DataObject;
use ThirdParty\BlogArticle\Api\Data\CategoryInterface;

class Category extends DataObject implements CategoryInterface
{
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID) !== null ? (int) $this->getData(self::CATEGORY_ID) : null;
    }

    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE) !== null ? (int) $this->getData(self::IS_ACTIVE) : null;
    }

    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER) !== null ? (int) $this->getData(self::SORT_ORDER) : null;
    }

    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, (int) $sortOrder);
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
