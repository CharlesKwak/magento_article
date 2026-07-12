<?php
namespace ThirdParty\BlogArticle\Controller\Rss;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

/**
 * Public RSS 2.0 feed of enabled blog posts.
 */
class Feed extends Action
{
    private $resultRawFactory;
    private $collectionFactory;
    private $postFilter;
    private $storeManager;
    private $imageUploader;

    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        CollectionFactory $collectionFactory,
        PostFilter $postFilter,
        StoreManagerInterface $storeManager,
        FeaturedImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->collectionFactory = $collectionFactory;
        $this->postFilter = $postFilter;
        $this->storeManager = $storeManager;
        $this->imageUploader = $imageUploader;
    }

    public function execute()
    {
        $store = $this->storeManager->getStore();
        $baseUrl = rtrim($store->getBaseUrl(), '/');
        $listUrl = $baseUrl . '/blog/';
        $feedUrl = $baseUrl . '/blog/rss/feed/';

        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applyStoreId($collection, (int) $store->getId());
        $this->postFilter->applyDefaultSort($collection);
        $collection->setPageSize(50);

        $itemsXml = '';
        foreach ($collection as $post) {
            $urlKey = (string) $post->getUrlKey();
            $link = $urlKey !== ''
                ? $baseUrl . '/blog/' . rawurlencode($urlKey)
                : $baseUrl . '/blog/post/view/id/' . (int) $post->getId();
            $pubDate = $post->getPublishedAt() ?: $post->getCreationTime();
            $pubTs = $pubDate ? strtotime((string) $pubDate) : time();
            $description = trim((string) $post->getExcerpt());
            if ($description === '') {
                $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->getContent())) ?? '');
                if (strlen($description) > 300) {
                    $description = substr($description, 0, 300) . '...';
                }
            }
            $image = $this->imageUploader->resolveUrl(
                $post->getFeaturedImage() ? (string) $post->getFeaturedImage() : null
            );
            if ($image !== '') {
                $description = '<p><img src="' . htmlspecialchars($image, ENT_XML1) . '" alt=""/></p>'
                    . $description;
            }

            $itemsXml .= '<item>'
                . '<title>' . $this->xml($post->getTitle()) . '</title>'
                . '<link>' . $this->xml($link) . '</link>'
                . '<guid isPermaLink="true">' . $this->xml($link) . '</guid>'
                . '<pubDate>' . $this->xml(date(DATE_RSS, $pubTs ?: time())) . '</pubDate>'
                . '<description>' . $this->xml($description) . '</description>'
                . '</item>';
        }

        $channelTitle = (string) __('Blog');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<rss version="2.0">'
            . '<channel>'
            . '<title>' . $this->xml($channelTitle) . '</title>'
            . '<link>' . $this->xml($listUrl) . '</link>'
            . '<description>' . $this->xml((string) __('Latest blog posts')) . '</description>'
            . '<atom:link xmlns:atom="http://www.w3.org/2005/Atom" href="'
            . $this->xml($feedUrl) . '" rel="self" type="application/rss+xml"/>'
            . $itemsXml
            . '</channel></rss>';

        $result = $this->resultRawFactory->create();
        $result->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8', true);
        $result->setContents($xml);
        return $result;
    }

    private function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
