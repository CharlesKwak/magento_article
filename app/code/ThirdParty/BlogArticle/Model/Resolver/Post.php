<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;

class Post implements ResolverInterface
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
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

        return [
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
            'store_id' => $item->getStoreId(),
            'tag_ids' => $item->getTagIds() ?: [],
            'creation_time' => $item->getCreationTime(),
            'update_time' => $item->getUpdateTime(),
            'published_at' => $item->getPublishedAt(),
            'model' => $item,
        ];
    }
}
