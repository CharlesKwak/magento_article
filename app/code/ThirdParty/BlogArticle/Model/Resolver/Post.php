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
use ThirdParty\BlogArticle\Model\Resolver\DataMapper\PostMapper;

class Post implements ResolverInterface
{
    private PostRepositoryInterface $postRepository;
    private PostMapper $postMapper;

    public function __construct(
        PostRepositoryInterface $postRepository,
        PostMapper $postMapper
    ) {
        $this->postRepository = $postRepository;
        $this->postMapper = $postMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $id = isset($args['id']) ? (int) $args['id'] : 0;
        $urlKey = isset($args['url_key']) ? trim((string) $args['url_key']) : '';

        if (!$id && $urlKey === '') {
            throw new GraphQlInputException(__('Specify id or url_key.'));
        }

        try {
            $item = $id
                ? $this->postRepository->getById($id)
                : $this->postRepository->getByUrlKey($urlKey);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $this->postMapper->toGraphQlArray($item);
    }
}
