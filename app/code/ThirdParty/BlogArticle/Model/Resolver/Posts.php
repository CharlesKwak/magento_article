<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\Resolver\DataMapper\PostMapper;

class Posts implements ResolverInterface
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

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $pageSize = isset($args['pageSize']) ? (int) $args['pageSize'] : 10;
        $currentPage = isset($args['currentPage']) ? (int) $args['currentPage'] : 1;
        $search = isset($args['search']) ? (string) $args['search'] : null;
        $categoryId = isset($args['categoryId']) ? (int) $args['categoryId'] : null;
        $tagId = isset($args['tagId']) ? (int) $args['tagId'] : null;
        $author = isset($args['author']) ? trim((string) $args['author']) : null;
        if ($author === '') {
            $author = null;
        }
        $year = isset($args['year']) ? (int) $args['year'] : null;
        $month = isset($args['month']) ? (int) $args['month'] : null;
        if ($year !== null && ($year < 1970 || $year > 2100)) {
            $year = null;
            $month = null;
        }
        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }
        $sort = isset($args['sort']) ? trim((string) $args['sort']) : null;
        if ($sort === '') {
            $sort = null;
        }

        $pageSize = max(1, min(100, $pageSize));
        $currentPage = max(1, $currentPage);

        $items = $this->postRepository->getList(
            $currentPage,
            $pageSize,
            $search,
            $categoryId,
            $tagId,
            $author,
            $year,
            $month,
            $sort
        );
        $totalCount = $this->postRepository->getListTotalCount(
            $search,
            $categoryId,
            $tagId,
            $author,
            $year,
            $month
        );
        $totalPages = $pageSize > 0 ? (int) ceil($totalCount / $pageSize) : 0;

        $mapped = [];
        foreach ($items as $item) {
            $row = $this->postMapper->toGraphQlArray($item);
            $mapped[] = $row;
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
