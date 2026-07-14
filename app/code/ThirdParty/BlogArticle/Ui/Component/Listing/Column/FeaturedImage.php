<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;

/**
 * Renders a small featured-image thumbnail in the Admin post grid.
 */
class FeaturedImage extends Column
{
    private FeaturedImageUploader $imageUploader;
    private Escaper $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FeaturedImageUploader $imageUploader,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->imageUploader = $imageUploader;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $raw = isset($item['featured_image']) ? (string) $item['featured_image'] : '';
            $url = $this->imageUploader->resolveUrl($raw !== '' ? $raw : null);
            if ($url === '') {
                $item[$fieldName] = '';
                continue;
            }
            $safeUrl = $this->escaper->escapeUrl($url);
            $title = isset($item['title']) ? (string) $item['title'] : '';
            $alt = $this->escaper->escapeHtmlAttr($title !== '' ? $title : __('Featured image'));
            $item[$fieldName] = sprintf(
                '<img src="%s" alt="%s" style="max-width:60px;max-height:40px;width:auto;height:auto;object-fit:cover;border-radius:2px;vertical-align:middle;" loading="lazy"/>',
                $safeUrl,
                $alt
            );
        }

        return $dataSource;
    }
}
