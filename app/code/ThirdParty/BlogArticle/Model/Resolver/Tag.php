<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\TagRepositoryInterface;

class Tag implements ResolverInterface
{
    private TagRepositoryInterface $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $id = isset($args['id']) ? (int) $args['id'] : 0;
        $urlKey = isset($args['url_key']) ? trim((string) $args['url_key']) : '';
        if (!$id && $urlKey === '') {
            throw new GraphQlInputException(__('Specify id or url_key.'));
        }
        try {
            $tag = $id
                ? $this->tagRepository->getById($id)
                : $this->tagRepository->getByUrlKey($urlKey);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return [
            'tag_id' => $tag->getTagId(),
            'name' => $tag->getName(),
            'url_key' => $tag->getUrlKey(),
            'description' => $tag->getDescription(),
            'is_active' => $tag->getIsActive(),
            'creation_time' => $tag->getCreationTime(),
            'update_time' => $tag->getUpdateTime(),
        ];
    }
}
