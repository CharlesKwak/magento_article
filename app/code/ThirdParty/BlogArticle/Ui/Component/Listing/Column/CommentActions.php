<?php
namespace ThirdParty\BlogArticle\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CommentActions extends Column
{
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['comment_id'])) {
                continue;
            }
            if (empty($item['is_approved'])) {
                $item[$name]['approve'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'blogarticle/comment/approve',
                        ['comment_id' => $item['comment_id']]
                    ),
                    'label' => __('Approve'),
                ];
            }
            $item[$name]['delete'] = [
                'href' => $this->urlBuilder->getUrl(
                    'blogarticle/comment/delete',
                    ['comment_id' => $item['comment_id']]
                ),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete comment'),
                    'message' => __('Are you sure you want to delete this comment?'),
                ],
            ];
        }
        return $dataSource;
    }
}
