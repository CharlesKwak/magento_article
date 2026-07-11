<?php
namespace ThirdParty\BlogArticle\Model;

use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory;

class CategoryUrlKeyGenerator
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var UrlKeyGenerator
     */
    private $urlKeyGenerator;

    public function __construct(
        CollectionFactory $collectionFactory,
        UrlKeyGenerator $urlKeyGenerator
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->urlKeyGenerator = $urlKeyGenerator;
    }

    /**
     * @param string $titleOrKey
     * @param int|null $excludeCategoryId
     * @return string
     */
    public function generate(string $titleOrKey, ?int $excludeCategoryId = null): string
    {
        $base = $this->urlKeyGenerator->slugify($titleOrKey);
        if ($base === '') {
            $base = 'category';
        }

        $candidate = $base;
        $suffix = 1;
        while ($this->exists($candidate, $excludeCategoryId)) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }
        return $candidate;
    }

    /**
     * @param string $urlKey
     * @param int|null $excludeCategoryId
     * @return bool
     */
    private function exists(string $urlKey, ?int $excludeCategoryId = null): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('url_key', $urlKey);
        if ($excludeCategoryId) {
            $collection->addFieldToFilter('category_id', ['neq' => $excludeCategoryId]);
        }
        return (bool) $collection->getSize();
    }
}
