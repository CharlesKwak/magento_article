<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Api\Data;

interface CommentInterface
{
    public const COMMENT_ID = 'comment_id';
    public const POST_ID = 'post_id';
    public const PARENT_ID = 'parent_id';
    public const AUTHOR_NAME = 'author_name';
    public const AUTHOR_EMAIL = 'author_email';
    public const CONTENT = 'content';
    public const IS_APPROVED = 'is_approved';
    public const CREATION_TIME = 'creation_time';

    /** @return int|null */
    public function getCommentId();
    /** @param int $commentId @return $this */
    public function setCommentId($commentId);
    /** @return int|null */
    public function getPostId();
    /** @param int $postId @return $this */
    public function setPostId($postId);
    /** @return int|null */
    public function getParentId();
    /** @param int|null $parentId @return $this */
    public function setParentId($parentId);
    /** @return string|null */
    public function getAuthorName();
    /** @param string $authorName @return $this */
    public function setAuthorName($authorName);
    /** @return string|null */
    public function getAuthorEmail();
    /** @param string|null $authorEmail @return $this */
    public function setAuthorEmail($authorEmail);
    /** @return string|null */
    public function getContent();
    /** @param string $content @return $this */
    public function setContent($content);
    /** @return int|null */
    public function getIsApproved();
    /** @param int $isApproved @return $this */
    public function setIsApproved($isApproved);
    /** @return string|null */
    public function getCreationTime();
    /** @param string $creationTime @return $this */
    public function setCreationTime($creationTime);
}
