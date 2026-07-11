<?php
namespace ThirdParty\BlogArticle\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\Data\PostInterface;

/**
 * Public read API for blog posts.
 */
interface PostRepositoryInterface
{
    /**
     * Get post by ID (enabled only for anonymous storefront use).
     *
     * @param int $postId
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface
     * @throws NoSuchEntityException
     */
    public function getById($postId);

    /**
     * Get post by URL key (enabled only).
     *
     * @param string $urlKey
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey($urlKey);

    /**
     * List enabled posts, newest first.
     *
     * @param int $page
     * @param int $pageSize
     * @param string|null $search Free-text filter on title, content, url_key
     * @return \ThirdParty\BlogArticle\Api\Data\PostInterface[]
     */
    public function getList($page = 1, $pageSize = 10, $search = null);

    /**
     * Total enabled posts matching optional search (ignores pagination).
     *
     * @param string|null $search
     * @return int
     */
    public function getListTotalCount($search = null);
}
