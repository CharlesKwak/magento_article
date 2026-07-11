<?php
namespace ThirdParty\BlogArticle\Model;

use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class UrlKeyGenerator
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Build a unique URL key from a title (or provided key).
     *
     * @param string $titleOrKey
     * @param int|null $excludePostId
     * @return string
     */
    public function generate(string $titleOrKey, ?int $excludePostId = null): string
    {
        $base = $this->slugify($titleOrKey);
        if ($base === '') {
            $base = 'post';
        }

        $candidate = $base;
        $suffix = 1;
        while ($this->exists($candidate, $excludePostId)) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * @param string $value
     * @return string
     */
    public function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }

    /**
     * @param string $urlKey
     * @param int|null $excludePostId
     * @return bool
     */
    private function exists(string $urlKey, ?int $excludePostId = null): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('url_key', $urlKey);
        if ($excludePostId) {
            $collection->addFieldToFilter('post_id', ['neq' => $excludePostId]);
        }
        return (bool) $collection->getSize();
    }
}
