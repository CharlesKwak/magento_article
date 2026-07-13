<?php
namespace ThirdParty\BlogArticle\Api\Data;

/**
 * Monthly archive bucket for REST/headless clients.
 */
interface BlogArchiveMonthInterface
{
    /**
     * @return int
     */
    public function getYear();

    /**
     * @param int $year
     * @return $this
     */
    public function setYear($year);

    /**
     * @return int
     */
    public function getMonth();

    /**
     * @param int $month
     * @return $this
     */
    public function setMonth($month);

    /**
     * @return int
     */
    public function getCount();

    /**
     * @param int $count
     * @return $this
     */
    public function setCount($count);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Relative path e.g. blog/archive/2026/07
     *
     * @return string
     */
    public function getUrlPath();

    /**
     * @param string $urlPath
     * @return $this
     */
    public function setUrlPath($urlPath);

    /**
     * Absolute storefront URL
     *
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url);
}
