<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Many-to-many link between blog posts and catalog products (by product ID).
 */
class PostProductLink
{
    private ResourceConnection $resource;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        ResourceConnection $resource,
        ProductRepositoryInterface $productRepository
    ) {
        $this->resource = $resource;
        $this->productRepository = $productRepository;
    }

    /**
     * @return int[]
     */
    public function getProductIdsForPost(int $postId): array
    {
        if ($postId <= 0 || !$this->tableExists()) {
            return [];
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post_product');
        $select = $connection->select()
            ->from($table, ['product_id'])
            ->where('post_id = ?', $postId);
        return array_map('intval', $connection->fetchCol($select));
    }

    /**
     * @return int[]
     */
    public function getPostIdsForProduct(int $productId): array
    {
        if ($productId <= 0 || !$this->tableExists()) {
            return [];
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post_product');
        $select = $connection->select()
            ->from($table, ['post_id'])
            ->where('product_id = ?', $productId);
        return array_map('intval', $connection->fetchCol($select));
    }

    /**
     * Replace product links for a post using comma/space-separated SKUs or IDs.
     */
    public function setProductsFromInput(int $postId, string $input): void
    {
        if ($postId <= 0 || !$this->tableExists()) {
            return;
        }
        $tokens = preg_split('/[\s,;]+/', trim($input), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $productIds = [];
        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if ($token === '') {
                continue;
            }
            try {
                if (ctype_digit($token)) {
                    $product = $this->productRepository->getById((int) $token);
                } else {
                    $product = $this->productRepository->get($token);
                }
                $productIds[] = (int) $product->getId();
            } catch (NoSuchEntityException $e) {
                // skip unknown SKU/ID
            } catch (\Throwable $e) {
                // catalog may be unavailable in some contexts
            }
        }
        $productIds = array_values(array_unique(array_filter($productIds)));
        $this->replaceLinks($postId, $productIds);
    }

    /**
     * @param int[] $productIds
     */
    public function replaceLinks(int $postId, array $productIds): void
    {
        if ($postId <= 0 || !$this->tableExists()) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('thirdparty_blogarticle_post_product');
        $connection->delete($table, ['post_id = ?' => $postId]);
        foreach ($productIds as $productId) {
            $productId = (int) $productId;
            if ($productId <= 0) {
                continue;
            }
            $connection->insert($table, [
                'post_id' => $postId,
                'product_id' => $productId,
            ]);
        }
    }

    /**
     * SKUs currently linked (for admin form display).
     *
     * @return string[]
     */
    public function getProductSkusForPost(int $postId): array
    {
        $skus = [];
        foreach ($this->getProductIdsForPost($postId) as $productId) {
            try {
                $skus[] = (string) $this->productRepository->getById($productId)->getSku();
            } catch (\Throwable $e) {
                $skus[] = (string) $productId;
            }
        }
        return $skus;
    }

    private function tableExists(): bool
    {
        $connection = $this->resource->getConnection();
        return $connection->isTableExists(
            $this->resource->getTableName('thirdparty_blogarticle_post_product')
        );
    }
}
