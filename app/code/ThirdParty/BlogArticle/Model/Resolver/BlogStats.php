<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\BlogStatsManagementInterface;
use ThirdParty\BlogArticle\Model\GraphQl\Authorization;

/**
 * Admin/integration GraphQL stats for dashboard-style UIs.
 */
class BlogStats implements ResolverInterface
{
    private Authorization $authorization;
    private BlogStatsManagementInterface $statsManagement;

    public function __construct(
        Authorization $authorization,
        BlogStatsManagementInterface $statsManagement
    ) {
        $this->authorization = $authorization;
        $this->statsManagement = $statsManagement;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->authorization->assertCanWrite($context);
        $stats = $this->statsManagement->get();
        return [
            'posts_total' => $stats->getPostsTotal(),
            'posts_enabled' => $stats->getPostsEnabled(),
            'comments_total' => $stats->getCommentsTotal(),
            'comments_pending' => $stats->getCommentsPending(),
            'categories_total' => $stats->getCategoriesTotal(),
            'tags_total' => $stats->getTagsTotal(),
            'views_total' => $stats->getViewsTotal(),
        ];
    }
}
