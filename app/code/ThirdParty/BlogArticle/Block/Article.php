<?php
namespace ThirdParty\BlogArticle\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class Article extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Active posts only, newest first.
     *
     * @return Collection
     */
    public function getPosts()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('creation_time', 'DESC');
        return $collection;
    }

    /**
     * @param Post $post
     * @return string
     */
    public function getPostUrl(Post $post): string
    {
        $params = [];
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            $params['url_key'] = $urlKey;
        } else {
            $params['id'] = (int) $post->getId();
        }

        return $this->getUrl('blog/post/view', $params);
    }

    /**
     * @param Post $post
     * @param int $length
     * @return string
     */
    public function getExcerpt(Post $post, int $length = 200): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->getContent())) ?? '');
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $length)) . '…';
    }
}
