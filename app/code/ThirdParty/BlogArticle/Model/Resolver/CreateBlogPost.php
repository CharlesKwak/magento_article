<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\Data\PostInterfaceFactory;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\GraphQl\Authorization;
use ThirdParty\BlogArticle\Model\Resolver\DataMapper\PostMapper;

class CreateBlogPost implements ResolverInterface
{
    private $authorization;
    private $postRepository;
    private $postFactory;
    private $postMapper;

    public function __construct(
        Authorization $authorization,
        PostRepositoryInterface $postRepository,
        PostInterfaceFactory $postFactory,
        PostMapper $postMapper
    ) {
        $this->authorization = $authorization;
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        $this->postMapper = $postMapper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->authorization->assertCanWrite($context);
        $input = $args['input'] ?? null;
        if (!is_array($input)) {
            throw new GraphQlInputException(__('"input" is required.'));
        }
        $post = $this->postFactory->create();
        $this->postMapper->mapInputToPost($input, $post);
        $saved = $this->postRepository->save($post);
        return $this->postMapper->toGraphQlArray($saved);
    }
}
