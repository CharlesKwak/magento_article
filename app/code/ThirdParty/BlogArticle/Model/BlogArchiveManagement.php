<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Api\BlogArchiveManagementInterface;
use ThirdParty\BlogArticle\Api\Data\BlogArchiveMonthInterfaceFactory;

class BlogArchiveManagement implements BlogArchiveManagementInterface
{
    private Archive $archive;
    private StoreManagerInterface $storeManager;
    private UrlInterface $urlBuilder;
    private BlogArchiveMonthInterfaceFactory $monthFactory;

    public function __construct(
        Archive $archive,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        BlogArchiveMonthInterfaceFactory $monthFactory
    ) {
        $this->archive = $archive;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->monthFactory = $monthFactory;
    }

    public function getList($limit = 24)
    {
        $limit = max(1, min(120, (int) $limit));
        $storeId = (int) $this->storeManager->getStore()->getId();
        $items = [];
        foreach ($this->archive->getMonthlyBuckets($limit, $storeId) as $bucket) {
            $year = (int) $bucket['year'];
            $month = (int) $bucket['month'];
            $path = sprintf('blog/archive/%04d/%02d', $year, $month);
            /** @var \ThirdParty\BlogArticle\Api\Data\BlogArchiveMonthInterface $row */
            $row = $this->monthFactory->create();
            $row->setYear($year);
            $row->setMonth($month);
            $row->setCount((int) $bucket['count']);
            $row->setLabel((string) $bucket['label']);
            $row->setUrlPath($path);
            $row->setUrl($this->urlBuilder->getUrl('', ['_direct' => $path]));
            $items[] = $row;
        }
        return $items;
    }
}
