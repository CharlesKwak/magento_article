<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\TagRepositoryInterface;

class Tags implements ResolverInterface
{
    private $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $items = [];
        foreach ($this->tagRepository->getList(true) as $tag) {
            $items[] = [
                'tag_id' => $tag->getTagId(),
                'name' => $tag->getName(),
                'url_key' => $tag->getUrlKey(),
                'description' => $tag->getDescription(),
                'is_active' => $tag->getIsActive(),
                'creation_time' => $tag->getCreationTime(),
                'update_time' => $tag->getUpdateTime(),
            ];
        }
        return $items;
    }
}
