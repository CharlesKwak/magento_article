<?php
namespace ThirdParty\BlogArticle\Model\Data;

use Magento\Framework\DataObject;
use ThirdParty\BlogArticle\Api\Data\CommentInterface;

class Comment extends DataObject implements CommentInterface
{
    public function getCommentId()
    {
        return $this->getData(self::COMMENT_ID) !== null ? (int) $this->getData(self::COMMENT_ID) : null;
    }

    public function setCommentId($commentId)
    {
        return $this->setData(self::COMMENT_ID, $commentId);
    }

    public function getPostId()
    {
        return $this->getData(self::POST_ID) !== null ? (int) $this->getData(self::POST_ID) : null;
    }

    public function setPostId($postId)
    {
        return $this->setData(self::POST_ID, $postId);
    }

    public function getAuthorName()
    {
        return $this->getData(self::AUTHOR_NAME);
    }

    public function setAuthorName($authorName)
    {
        return $this->setData(self::AUTHOR_NAME, $authorName);
    }

    public function getAuthorEmail()
    {
        return $this->getData(self::AUTHOR_EMAIL);
    }

    public function setAuthorEmail($authorEmail)
    {
        return $this->setData(self::AUTHOR_EMAIL, $authorEmail);
    }

    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    public function getIsApproved()
    {
        return $this->getData(self::IS_APPROVED) !== null ? (int) $this->getData(self::IS_APPROVED) : null;
    }

    public function setIsApproved($isApproved)
    {
        return $this->setData(self::IS_APPROVED, $isApproved);
    }

    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }
}
