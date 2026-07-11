<?php
namespace ThirdParty\BlogArticle\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\Data\PostInterface;

/**
 * Blog post repository (public read + authenticated write).
 */
interface PostRepositoryInterface
{
    /**
     * @param int $postId
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface
     * @throws NoSuchEntityException
     */
    public function getById($postId, $activeOnly = true);

    /**
     * @param string $urlKey
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey($urlKey, $activeOnly = true);

    /**
     * @param int $page
     * @param int $pageSize
     * @param string|null $search
     * @param int|null $categoryId
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface[]
     */
    public function getList($page = 1, $pageSize = 10, $search = null, $categoryId = null);

    /**
     * @param string|null $search
     * @param int|null $categoryId
     * @return int
     */
    public function getListTotalCount($search = null, $categoryId = null);

    /**
     * Create or update a post (Admin token required via webapi ACL).
     *
     * @param \ThirdParty\BlogArticle\Api\Data\PostInterface $post
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface
     * @throws LocalizedException
     */
    public function save(PostInterface $post);

    /**
     * @param int $postId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($postId);
}
