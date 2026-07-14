<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;

class TagUrlKeyGenerator
{
    private CollectionFactory $collectionFactory;
    private UrlKeyGenerator $urlKeyGenerator;

    public function __construct(CollectionFactory $collectionFactory, UrlKeyGenerator $urlKeyGenerator)
    {
        $this->collectionFactory = $collectionFactory;
        $this->urlKeyGenerator = $urlKeyGenerator;
    }

    public function generate(string $titleOrKey, ?int $excludeTagId = null): string
    {
        $base = $this->urlKeyGenerator->slugify($titleOrKey);
        if ($base === '') {
            $base = 'tag';
        }
        $candidate = $base;
        $suffix = 1;
        while ($this->exists($candidate, $excludeTagId)) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }
        return $candidate;
    }

    private function exists(string $urlKey, ?int $excludeTagId = null): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('url_key', $urlKey);
        if ($excludeTagId) {
            $collection->addFieldToFilter('tag_id', ['neq' => $excludeTagId]);
        }
        return (bool) $collection->getSize();
    }
}
