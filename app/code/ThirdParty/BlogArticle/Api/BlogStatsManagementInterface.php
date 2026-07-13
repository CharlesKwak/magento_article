<?php
namespace ThirdParty\BlogArticle\Api;

/**
 * Blog aggregate statistics for Admin / integration tokens.
 */
interface BlogStatsManagementInterface
{
    /**
     * @return \ThirdParty\BlogArticle\Api\Data\BlogStatsInterface
     */
    public function get();
}
