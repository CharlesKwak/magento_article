<?php
namespace ThirdParty\BlogArticle\Api\Data;

/**
 * Aggregate blog metrics for Admin / integration dashboards.
 */
interface BlogStatsInterface
{
    /**
     * @return int
     */
    public function getPostsTotal();

    /**
     * @param int $value
     * @return $this
     */
    public function setPostsTotal($value);

    /**
     * @return int
     */
    public function getPostsEnabled();

    /**
     * @param int $value
     * @return $this
     */
    public function setPostsEnabled($value);

    /**
     * @return int
     */
    public function getCommentsTotal();

    /**
     * @param int $value
     * @return $this
     */
    public function setCommentsTotal($value);

    /**
     * @return int
     */
    public function getCommentsPending();

    /**
     * @param int $value
     * @return $this
     */
    public function setCommentsPending($value);

    /**
     * @return int
     */
    public function getCategoriesTotal();

    /**
     * @param int $value
     * @return $this
     */
    public function setCategoriesTotal($value);

    /**
     * @return int
     */
    public function getTagsTotal();

    /**
     * @param int $value
     * @return $this
     */
    public function setTagsTotal($value);

    /**
     * @return int
     */
    public function getViewsTotal();

    /**
     * @param int $value
     * @return $this
     */
    public function setViewsTotal($value);
}
