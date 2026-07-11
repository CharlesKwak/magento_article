<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;
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

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection
     */
    public function getPosts()
    {
        return $this->collectionFactory->create();
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
}
