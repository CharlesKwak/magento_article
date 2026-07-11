<?php
namespace ThirdParty\BlogArticle\Block\Post;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFactory;

class View extends Template
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var Post|null
     */
    private $post;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Post|null
     */
    public function getPost()
    {
        if ($this->post !== null) {
            return $this->post;
        }

        $post = $this->postFactory->create();
        $urlKey = (string) $this->getRequest()->getParam('url_key', '');
        $id = (int) $this->getRequest()->getParam('id', 0);

        if ($urlKey !== '') {
            $post->load($urlKey, 'url_key');
        } elseif ($id) {
            $post->load($id);
        }

        if (!$post->getId() || !(int) $post->getIsActive()) {
            $this->post = null;
            return null;
        }

        $this->post = $post;
        return $this->post;
    }

    /**
     * @return string
     */
    public function getListUrl(): string
    {
        return $this->getUrl('blog/index/index');
    }

    /**
     * Canonical clean URL for the current post when url_key is set.
     *
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        $post = $this->getPost();
        if (!$post) {
            return '';
        }
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            return $this->getUrl('', ['_direct' => 'blog/' . $urlKey]);
        }
        return $this->getUrl('blog/post/view', ['id' => (int) $post->getId()]);
    }

    /**
     * @return string[]
     */
    public function getAllowedContentTags(): array
    {
        return ['p', 'br', 'em', 'strong', 'b', 'i', 'ul', 'ol', 'li', 'a', 'h2', 'h3', 'h4'];
    }
}
