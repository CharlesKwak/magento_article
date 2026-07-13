<?php
namespace ThirdParty\BlogArticle\Controller\Rss;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\Config;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;
use ThirdParty\BlogArticle\Model\TagFactory;

/**
 * Public RSS 2.0 feed of enabled blog posts.
 *
 * Optional filters (query or path params):
 *  - category / cat: category url_key
 *  - tag: tag url_key
 *  - author: author slug or name
 *  - year / month: archive calendar filters
 */
class Feed extends Action
{
    private $resultRawFactory;
    private $collectionFactory;
    private $postFilter;
    private $storeManager;
    private $imageUploader;
    private $config;
    private $categoryFactory;
    private $tagFactory;

    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        CollectionFactory $collectionFactory,
        PostFilter $postFilter,
        StoreManagerInterface $storeManager,
        FeaturedImageUploader $imageUploader,
        Config $config,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->collectionFactory = $collectionFactory;
        $this->postFilter = $postFilter;
        $this->storeManager = $storeManager;
        $this->imageUploader = $imageUploader;
        $this->config = $config;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
    }

    public function execute()
    {
        $store = $this->storeManager->getStore();
        $baseUrl = rtrim($store->getBaseUrl(), '/');
        $listUrl = $baseUrl . '/blog/';
        $blogName = $this->config->getBlogName();

        $categoryKey = trim((string) $this->getRequest()->getParam('category', ''));
        if ($categoryKey === '') {
            $categoryKey = trim((string) $this->getRequest()->getParam('cat', ''));
        }
        $tagKey = trim((string) $this->getRequest()->getParam('tag', ''));
        $authorKey = trim((string) $this->getRequest()->getParam('author', ''));
        $year = (int) $this->getRequest()->getParam('year', 0);
        $month = (int) $this->getRequest()->getParam('month', 0);
        if ($year < 1970 || $year > 2100) {
            $year = 0;
            $month = 0;
        }
        if ($month < 1 || $month > 12) {
            $month = 0;
        }

        $feedQuery = [];
        $channelTitle = $blogName;
        $channelDesc = (string) __('Latest blog posts');
        $categoryId = null;
        $tagId = null;

        if ($categoryKey !== '') {
            $category = $this->categoryFactory->create()->load($categoryKey, 'url_key');
            if ($category->getId() && (int) $category->getIsActive()) {
                $categoryId = (int) $category->getId();
                $feedQuery['category'] = $categoryKey;
                $channelTitle = (string) __('%1 — %2', $blogName, $category->getName());
                $channelDesc = (string) __('Posts in category %1', $category->getName());
                $listUrl = $baseUrl . '/blog/category/' . rawurlencode($categoryKey);
            }
        }
        if ($tagKey !== '') {
            $tag = $this->tagFactory->create()->load($tagKey, 'url_key');
            if ($tag->getId() && (int) $tag->getIsActive()) {
                $tagId = (int) $tag->getId();
                $feedQuery['tag'] = $tagKey;
                $channelTitle = (string) __('%1 — #%2', $blogName, $tag->getName());
                $channelDesc = (string) __('Posts tagged %1', $tag->getName());
                $listUrl = $baseUrl . '/blog/tag/' . rawurlencode($tagKey);
            }
        }
        if ($authorKey !== '') {
            $feedQuery['author'] = $authorKey;
            $channelTitle = (string) __('%1 — Author %2', $blogName, str_replace('-', ' ', $authorKey));
            $channelDesc = (string) __('Posts by %1', str_replace('-', ' ', $authorKey));
            $listUrl = $baseUrl . '/blog/author/' . rawurlencode($authorKey);
        }
        if ($year > 0) {
            $feedQuery['year'] = $year;
            if ($month > 0) {
                $feedQuery['month'] = $month;
                try {
                    $label = (new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('F Y');
                } catch (\Exception $e) {
                    $label = sprintf('%04d-%02d', $year, $month);
                }
                $channelTitle = (string) __('%1 — Archive %2', $blogName, $label);
                $channelDesc = (string) __('Posts from %1', $label);
                $listUrl = $baseUrl . sprintf('/blog/archive/%04d/%02d', $year, $month);
            } else {
                $channelTitle = (string) __('%1 — Archive %2', $blogName, (string) $year);
                $channelDesc = (string) __('Posts from %1', (string) $year);
                $listUrl = $baseUrl . sprintf('/blog/archive/%04d', $year);
            }
        }

        $feedUrl = $baseUrl . '/blog/rss/feed/';
        if ($feedQuery) {
            $feedUrl .= '?' . http_build_query($feedQuery);
        }

        $collection = $this->collectionFactory->create();
        $this->postFilter->applyActiveOnly($collection);
        $this->postFilter->applyPublishedOnly($collection);
        $this->postFilter->applyStoreId($collection, (int) $store->getId());
        $this->postFilter->applyCategoryId($collection, $categoryId);
        $this->postFilter->applyTagId($collection, $tagId);
        $this->postFilter->applyAuthorKey($collection, $authorKey !== '' ? $authorKey : null);
        $this->postFilter->applyYearMonth(
            $collection,
            $year > 0 ? $year : null,
            $month > 0 ? $month : null
        );
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

            $author = trim((string) $post->getAuthor());
            $itemsXml .= '<item>'
                . '<title>' . $this->xml($post->getTitle()) . '</title>'
                . '<link>' . $this->xml($link) . '</link>'
                . '<guid isPermaLink="true">' . $this->xml($link) . '</guid>'
                . '<pubDate>' . $this->xml(date(DATE_RSS, $pubTs ?: time())) . '</pubDate>'
                . ($author !== '' ? '<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">'
                    . $this->xml($author) . '</dc:creator>' : '')
                . '<description>' . $this->xml($description) . '</description>'
                . '</item>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'
            . '<channel>'
            . '<title>' . $this->xml($channelTitle) . '</title>'
            . '<link>' . $this->xml($listUrl) . '</link>'
            . '<description>' . $this->xml($channelDesc) . '</description>'
            . '<atom:link href="' . $this->xml($feedUrl) . '" rel="self" type="application/rss+xml"/>'
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
