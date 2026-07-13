<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\CategoryRepositoryInterface;

class Categories implements ResolverInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
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
        $items = [];
        foreach ($this->categoryRepository->getList(true) as $category) {
            $items[] = [
                'category_id' => $category->getCategoryId(),
                'name' => $category->getName(),
                'url_key' => $category->getUrlKey(),
                'description' => $category->getDescription(),
                'is_active' => $category->getIsActive(),
                'creation_time' => $category->getCreationTime(),
                'update_time' => $category->getUpdateTime(),
            ];
        }
        return $items;
    }
}
