<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Model\GraphQl\Authorization;

class ApproveBlogComment implements ResolverInterface
{
    private Authorization $authorization;
    private CommentRepositoryInterface $commentRepository;

    public function __construct(
        Authorization $authorization,
        CommentRepositoryInterface $commentRepository
    ) {
        $this->authorization = $authorization;
        $this->commentRepository = $commentRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->authorization->assertCanWrite($context);
        $commentId = isset($args['comment_id']) ? (int) $args['comment_id'] : 0;
        if ($commentId <= 0) {
            throw new GraphQlInputException(__('comment_id is required.'));
        }
        try {
            $saved = $this->commentRepository->approve($commentId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
        return [
            'comment_id' => $saved->getCommentId(),
            'post_id' => $saved->getPostId(),
            'parent_id' => $saved->getParentId(),
            'author_name' => $saved->getAuthorName(),
            'author_email' => $saved->getAuthorEmail(),
            'content' => $saved->getContent(),
            'is_approved' => $saved->getIsApproved(),
            'creation_time' => $saved->getCreationTime(),
        ];
    }
}
