<?php
namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use ThirdParty\BlogArticle\Api\CategoryRepositoryInterface;

class Category implements ResolverInterface
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
        $id = isset($args['id']) ? (int) $args['id'] : 0;
        $urlKey = isset($args['url_key']) ? trim((string) $args['url_key']) : '';

        if (!$id && $urlKey === '') {
            throw new GraphQlInputException(__('Specify id or url_key.'));
        }

        try {
            $category = $id
                ? $this->categoryRepository->getById($id)
                : $this->categoryRepository->getByUrlKey($urlKey);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return [
            'category_id' => $category->getCategoryId(),
            'name' => $category->getName(),
            'url_key' => $category->getUrlKey(),
            'is_active' => $category->getIsActive(),
            'creation_time' => $category->getCreationTime(),
            'update_time' => $category->getUpdateTime(),
        ];
    }
}
