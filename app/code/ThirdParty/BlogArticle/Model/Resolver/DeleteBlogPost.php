<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\GraphQl\Authorization;

class DeleteBlogPost implements ResolverInterface
{
    private $authorization;
    private $postRepository;

    public function __construct(
        Authorization $authorization,
        PostRepositoryInterface $postRepository
    ) {
        $this->authorization = $authorization;
        $this->postRepository = $postRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->authorization->assertCanWrite($context);
        $postId = isset($args['post_id']) ? (int) $args['post_id'] : 0;
        if ($postId <= 0) {
            throw new GraphQlInputException(__('post_id is required.'));
        }
        try {
            return (bool) $this->postRepository->deleteById($postId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }
}
