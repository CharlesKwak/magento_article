<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Data;

use Magento\Framework\DataObject;
use ThirdParty\BlogArticle\Api\Data\BlogStatsInterface;

class BlogStats extends DataObject implements BlogStatsInterface
{
    public function getPostsTotal()
    {
        return (int) $this->getData('posts_total');
    }

    public function setPostsTotal($value)
    {
        return $this->setData('posts_total', (int) $value);
    }

    public function getPostsEnabled()
    {
        return (int) $this->getData('posts_enabled');
    }

    public function setPostsEnabled($value)
    {
        return $this->setData('posts_enabled', (int) $value);
    }

    public function getCommentsTotal()
    {
        return (int) $this->getData('comments_total');
    }

    public function setCommentsTotal($value)
    {
        return $this->setData('comments_total', (int) $value);
    }

    public function getCommentsPending()
    {
        return (int) $this->getData('comments_pending');
    }

    public function setCommentsPending($value)
    {
        return $this->setData('comments_pending', (int) $value);
    }

    public function getCategoriesTotal()
    {
        return (int) $this->getData('categories_total');
    }

    public function setCategoriesTotal($value)
    {
        return $this->setData('categories_total', (int) $value);
    }

    public function getTagsTotal()
    {
        return (int) $this->getData('tags_total');
    }

    public function setTagsTotal($value)
    {
        return $this->setData('tags_total', (int) $value);
    }

    public function getViewsTotal()
    {
        return (int) $this->getData('views_total');
    }

    public function setViewsTotal($value)
    {
        return $this->setData('views_total', (int) $value);
    }
}
