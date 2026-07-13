<?php
namespace ThirdParty\BlogArticle\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class PostActions extends Column
{
    public const URL_PATH_EDIT = 'blogarticle/post/edit';
    public const URL_PATH_DELETE = 'blogarticle/post/delete';

    private $urlBuilder;
    private $storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['post_id'])) {
                continue;
            }
            $name = $this->getData('name');
            $item[$name]['edit'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['post_id' => $item['post_id']]),
                'label' => __('Edit'),
            ];
            $preview = $this->buildStorefrontUrl($item);
            if ($preview !== '') {
                $item[$name]['view'] = [
                    'href' => $preview,
                    'label' => __('View'),
                    'target' => '_blank',
                ];
            }
            $item[$name]['delete'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['post_id' => $item['post_id']]),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete post'),
                    'message' => __('Are you sure you want to delete this post?'),
                ],
            ];
        }
        return $dataSource;
    }

    private function buildStorefrontUrl(array $item): string
    {
        $urlKey = isset($item['url_key']) ? trim((string) $item['url_key']) : '';
        if ($urlKey === '') {
            return '';
        }
        try {
            $storeId = !empty($item['store_id']) ? (int) $item['store_id'] : 0;
            if ($storeId > 0) {
                $store = $this->storeManager->getStore($storeId);
            } else {
                $store = $this->storeManager->getDefaultStoreView()
                    ?: $this->storeManager->getStore();
            }
            return rtrim($store->getBaseUrl(), '/') . '/blog/' . ltrim($urlKey, '/');
        } catch (\Exception $e) {
            return '';
        }
    }
}
