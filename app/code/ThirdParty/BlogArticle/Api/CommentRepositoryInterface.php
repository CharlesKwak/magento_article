<?php
namespace ThirdParty\BlogArticle\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\Data\CommentInterface;

interface CommentRepositoryInterface
{
    /**
     * @param int $commentId
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface
     * @throws NoSuchEntityException
     */
    public function getById($commentId);

    /**
     * Approved comments for a post (public).
     *
     * @param int $postId
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface[]
     */
    public function getListByPostId($postId);

    /**
     * Admin list with optional status filter.
     *
     * @param string $status all|approved|pending
     * @param int|null $postId Optional post filter
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface[]
     * @throws LocalizedException
     */
    public function getList($status = 'all', $postId = null);

    /**
     * Submit a new comment (storefront). May require moderation.
     *
     * @param \ThirdParty\BlogArticle\Api\Data\CommentInterface $comment
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface
     * @throws LocalizedException
     */
    public function submit(CommentInterface $comment);

    /**
     * Admin save (approve/edit).
     *
     * @param \ThirdParty\BlogArticle\Api\Data\CommentInterface $comment
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface
     * @throws LocalizedException
     */
    public function save(CommentInterface $comment);

    /**
     * @param int $commentId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function deleteById($commentId);

    /**
     * @param int $commentId
     * @return \ThirdParty\BlogArticle\Api\Data\CommentInterface
     * @throws NoSuchEntityException
     */
    public function approve($commentId);
}
