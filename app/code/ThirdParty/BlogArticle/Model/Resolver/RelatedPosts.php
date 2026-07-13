<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\Resolver\DataMapper\PostMapper;

class RelatedPosts implements ResolverInterface
{
    private $postRepository;
    private $postMapper;

    public function __construct(
        PostRepositoryInterface $postRepository,
        PostMapper $postMapper
    ) {
        $this->postRepository = $postRepository;
        $this->postMapper = $postMapper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $postId = isset($value['post_id']) ? (int) $value['post_id'] : 0;
        if (!$postId) {
            return [];
        }
        $limit = isset($args['limit']) ? (int) $args['limit'] : 3;
        if ($limit < 1) {
            $limit = 3;
        }
        $limit = min(20, $limit);

        // Guard against accidental deep recursion: related_posts inside related_posts
        // is limited to a shallow response without nested related_posts field expansion cost.
        try {
            $items = $this->postRepository->getRelated($postId, $limit);
        } catch (\Exception $e) {
            return [];
        }
        $mapped = [];
        foreach ($items as $item) {
            $row = $this->postMapper->toGraphQlArray($item);
            // Prevent infinite nested related_posts resolution in clients that re-query the field.
            unset($row['model']);
            $mapped[] = $row;
        }
        return $mapped;
    }
}
