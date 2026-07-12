<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Model\Config;

class PostComments implements ResolverInterface
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
        if (!$this->config->isCommentsEnabled()) {
            return [];
        }
        $postId = isset($value['post_id']) ? (int) $value['post_id'] : 0;
        if ($postId <= 0) {
            return [];
        }
        $items = [];
        foreach ($this->commentRepository->getListByPostId($postId) as $comment) {
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
