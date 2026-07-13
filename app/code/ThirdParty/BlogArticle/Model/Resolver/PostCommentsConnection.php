<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Model\Config;

/**
 * Paginated comments for a BlogPost with total_count metadata.
 */
class PostCommentsConnection implements ResolverInterface
{
    private $commentRepository;
    private $config;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        Config $config
    ) {
        $this->commentRepository = $commentRepository;
        $this->config = $config;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $empty = [
            'items' => [],
            'total_count' => 0,
            'page_size' => 20,
            'current_page' => 1,
            'total_pages' => 0,
        ];
        if (!$this->config->isCommentsEnabled()) {
            return $empty;
        }
        $postId = isset($value['post_id']) ? (int) $value['post_id'] : 0;
        if ($postId <= 0) {
            return $empty;
        }

        $pageSize = isset($args['pageSize']) ? (int) $args['pageSize'] : 20;
        $currentPage = isset($args['currentPage']) ? (int) $args['currentPage'] : 1;
        if ($pageSize < 1) {
            $pageSize = 20;
        }
        $pageSize = min(100, $pageSize);
        $currentPage = max(1, $currentPage);

        $totalCount = $this->commentRepository->getListByPostIdTotalCount($postId);
        $totalPages = $pageSize > 0 ? (int) ceil($totalCount / $pageSize) : 0;
        if ($totalPages > 0 && $currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $items = [];
        foreach ($this->commentRepository->getListByPostId($postId, $currentPage, $pageSize) as $comment) {
            $items[] = [
                'comment_id' => $comment->getCommentId(),
                'post_id' => $comment->getPostId(),
                'parent_id' => $comment->getParentId(),
                'author_name' => $comment->getAuthorName(),
                'author_email' => $comment->getAuthorEmail(),
                'content' => $comment->getContent(),
                'is_approved' => $comment->getIsApproved(),
                'creation_time' => $comment->getCreationTime(),
            ];
        }

        return [
            'items' => $items,
            'total_count' => $totalCount,
            'page_size' => $pageSize,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
        ];
    }
}
