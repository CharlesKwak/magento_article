<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\Archive;

/**
 * Public monthly archive buckets for headless clients.
 */
class Archives implements ResolverInterface
{
    private Archive $archive;
    private StoreManagerInterface $storeManager;
    private UrlInterface $urlBuilder;

    public function __construct(
        Archive $archive,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        $this->archive = $archive;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $limit = isset($args['limit']) ? (int) $args['limit'] : 24;
        $limit = max(1, min(120, $limit));
        $storeId = (int) $this->storeManager->getStore()->getId();
        $items = [];
        foreach ($this->archive->getMonthlyBuckets($limit, $storeId) as $bucket) {
            $year = (int) $bucket['year'];
            $month = (int) $bucket['month'];
            $path = sprintf('blog/archive/%04d/%02d', $year, $month);
            $items[] = [
                'year' => $year,
                'month' => $month,
                'count' => (int) $bucket['count'],
                'label' => (string) $bucket['label'],
                'url_path' => $path,
                'url' => $this->urlBuilder->getUrl('', ['_direct' => $path]),
            ];
        }
        return $items;
    }
}
