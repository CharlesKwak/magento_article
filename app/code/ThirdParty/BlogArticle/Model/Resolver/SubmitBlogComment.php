<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Api\Data\CommentInterfaceFactory;

class SubmitBlogComment implements ResolverInterface
{
    private $commentRepository;
    private $commentFactory;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        CommentInterfaceFactory $commentFactory
    ) {
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $postId = isset($args['post_id']) ? (int) $args['post_id'] : 0;
        $author = isset($args['author_name']) ? trim((string) $args['author_name']) : '';
        $content = isset($args['content']) ? trim((string) $args['content']) : '';
        $email = isset($args['author_email']) ? trim((string) $args['author_email']) : '';

        if ($postId <= 0 || $author === '' || $content === '') {
            throw new GraphQlInputException(__('post_id, author_name and content are required.'));
        }

        try {
            $comment = $this->commentFactory->create();
            $comment->setPostId($postId);
            $comment->setAuthorName($author);
            $comment->setAuthorEmail($email !== '' ? $email : null);
            $comment->setContent($content);
            $saved = $this->commentRepository->submit($comment);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'comment_id' => $saved->getCommentId(),
            'post_id' => $saved->getPostId(),
            'author_name' => $saved->getAuthorName(),
            'author_email' => $saved->getAuthorEmail(),
            'content' => $saved->getContent(),
            'is_approved' => $saved->getIsApproved(),
            'creation_time' => $saved->getCreationTime(),
        ];
    }
}
