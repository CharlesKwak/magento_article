<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;

class Posts implements ResolverInterface
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
        $pageSize = isset($args['pageSize']) ? (int) $args['pageSize'] : 10;
        $currentPage = isset($args['currentPage']) ? (int) $args['currentPage'] : 1;
        $search = isset($args['search']) ? (string) $args['search'] : null;

        $pageSize = max(1, min(100, $pageSize));
        $currentPage = max(1, $currentPage);

        $items = $this->postRepository->getList($currentPage, $pageSize, $search);
        $totalCount = $this->postRepository->getListTotalCount($search);
        $totalPages = $pageSize > 0 ? (int) ceil($totalCount / $pageSize) : 0;

        $mapped = [];
        foreach ($items as $item) {
            $mapped[] = [
                'post_id' => $item->getPostId(),
                'title' => $item->getTitle(),
                'url_key' => $item->getUrlKey(),
                'content' => $item->getContent(),
                'is_active' => $item->getIsActive(),
                'creation_time' => $item->getCreationTime(),
                'update_time' => $item->getUpdateTime(),
                'model' => $item,
            ];
        }

        return [
            'items' => $mapped,
            'total_count' => $totalCount,
            'page_size' => $pageSize,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
        ];
    }
}
