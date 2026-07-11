<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class Post extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * Frontend URL builder (not Admin URL).
     *
     * @var UrlInterface
     */
    private $frontendUrlBuilder;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        UrlInterface $frontendUrlBuilder,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection
     */
    public function getPosts()
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder('post_id', 'DESC');
        return $collection;
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return string
     */
    public function getNewUrl()
    {
        return $this->getUrl('blogarticle/post/new');
    }

    /**
     * @param int $postId
     * @return string
     */
    public function getEditUrl($postId)
    {
        return $this->getUrl('blogarticle/post/edit', ['post_id' => (int) $postId]);
    }

    /**
     * @param int $postId
     * @return string
     */
    public function getDeleteUrl($postId)
    {
        return $this->getUrl(
            'blogarticle/post/delete',
            [
                'post_id' => (int) $postId,
                'form_key' => $this->getFormKey(),
            ]
        );
    }

    /**
     * Storefront preview URL for a post.
     *
     * @param \ThirdParty\BlogArticle\Model\Post $post
     * @return string
     */
    public function getFrontendViewUrl($post)
    {
        $params = ['_nosid' => true];
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            $params['url_key'] = $urlKey;
        } else {
            $params['id'] = (int) $post->getId();
        }
        return $this->frontendUrlBuilder->getUrl('blog/post/view', $params);
    }
}
