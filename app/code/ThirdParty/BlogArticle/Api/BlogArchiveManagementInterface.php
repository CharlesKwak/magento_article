<?php
namespace ThirdParty\BlogArticle\Api;

/**
 * Public monthly archive listing for REST clients.
 */
interface BlogArchiveManagementInterface
{
    /**
     * @param int $limit Max months to return (1–120). Default 24.
     * @return \ThirdParty\BlogArticle\Api\Data\BlogArchiveMonthInterface[]
     */
    public function getList($limit = 24);
}
