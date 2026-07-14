<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\GraphQl\Authorization;
use ThirdParty\BlogArticle\Model\Resolver\DataMapper\PostMapper;

class UpdateBlogPost implements ResolverInterface
{
    private Authorization $authorization;
    private PostRepositoryInterface $postRepository;
    private PostMapper $postMapper;

    public function __construct(
        Authorization $authorization,
        PostRepositoryInterface $postRepository,
        PostMapper $postMapper
    ) {
        $this->authorization = $authorization;
        $this->postRepository = $postRepository;
        $this->postMapper = $postMapper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->authorization->assertCanWrite($context);
        $postId = isset($args['post_id']) ? (int) $args['post_id'] : 0;
        $input = $args['input'] ?? null;
        if ($postId <= 0 || !is_array($input)) {
            throw new GraphQlInputException(__('post_id and input are required.'));
        }
        try {
            $post = $this->postRepository->getById($postId, false);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        $this->postMapper->mapInputToPost($input, $post, true);
        $post->setPostId($postId);
        $saved = $this->postRepository->save($post);
        return $this->postMapper->toGraphQlArray($saved);
    }
}
