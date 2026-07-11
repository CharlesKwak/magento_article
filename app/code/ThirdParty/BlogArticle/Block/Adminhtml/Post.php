<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use ThirdParty\BlogArticle\Model\PostFilter;
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
     * @var UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * @var PostFilter
     */
    private $postFilter;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        UrlInterface $frontendUrlBuilder,
        PostFilter $postFilter,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->postFilter = $postFilter;
        parent::__construct($context, $data);
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection
     */
    public function getPosts()
    {
        $collection = $this->collectionFactory->create();
        $this->postFilter->applySearch($collection, $this->getSearchQuery());

        $status = $this->getStatusFilter();
        if ($status === '1' || $status === '0') {
            $collection->addFieldToFilter('is_active', (int) $status);
        }

        $collection->setOrder('post_id', 'DESC');
        return $collection;
    }

    /**
     * @return string
     */
    public function getSearchQuery(): string
    {
        return trim((string) $this->getRequest()->getParam('q', ''));
    }

    /**
     * @return string empty|0|1
     */
    public function getStatusFilter(): string
    {
        $status = $this->getRequest()->getParam('status', '');
        return in_array($status, ['0', '1'], true) ? $status : '';
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
     * @return string
     */
    public function getFilterFormAction()
    {
        return $this->getUrl('blogarticle/post/index');
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
     * @param \ThirdParty\BlogArticle\Model\Post $post
     * @return string
     */
    public function getFrontendViewUrl($post)
    {
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            return $this->frontendUrlBuilder->getUrl('', ['_direct' => 'blog/' . $urlKey, '_nosid' => true]);
        }

        return $this->frontendUrlBuilder->getUrl(
            'blog/post/view',
            ['id' => (int) $post->getId(), '_nosid' => true]
        );
    }
}
