<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Model\Config;

class PostComments implements ResolverInterface
{
    private CommentRepositoryInterface $commentRepository;
    private Config $config;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        Config $config
    ) {
        $this->commentRepository = $commentRepository;
        $this->config = $config;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!$this->config->isCommentsEnabled()) {
            return [];
        }
        $postId = isset($value['post_id']) ? (int) $value['post_id'] : 0;
        if ($postId <= 0) {
            return [];
        }
        $pageSize = isset($args['pageSize']) ? (int) $args['pageSize'] : 0;
        $currentPage = isset($args['currentPage']) ? (int) $args['currentPage'] : 1;
        if ($pageSize < 1) {
            // Legacy: return all approved comments
            $page = 0;
            $size = 50;
        } else {
            $page = max(1, $currentPage);
            $size = min(100, $pageSize);
        }
        $items = [];
        foreach ($this->commentRepository->getListByPostId($postId, $page, $size) as $comment) {
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
        return $items;
    }
}
