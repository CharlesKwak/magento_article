<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;

class RelatedPosts implements ResolverInterface
{
    private $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $postId = isset($value['post_id']) ? (int) $value['post_id'] : 0;
        if (!$postId) {
            return [];
        }
        try {
            $items = $this->postRepository->getRelated($postId, 3);
        } catch (\Exception $e) {
            return [];
        }
        $mapped = [];
        foreach ($items as $item) {
            $mapped[] = [
                'post_id' => $item->getPostId(),
                'title' => $item->getTitle(),
                'url_key' => $item->getUrlKey(),
                'content' => $item->getContent(),
                'excerpt' => $item->getExcerpt(),
                'featured_image' => $item->getFeaturedImage(),
                'meta_title' => $item->getMetaTitle(),
                'meta_description' => $item->getMetaDescription(),
                'is_active' => $item->getIsActive(),
                'category_id' => $item->getCategoryId(),
                'tag_ids' => $item->getTagIds() ?: [],
                'creation_time' => $item->getCreationTime(),
                'update_time' => $item->getUpdateTime(),
                'published_at' => $item->getPublishedAt(),
            ];
        }
        return $mapped;
    }
}
