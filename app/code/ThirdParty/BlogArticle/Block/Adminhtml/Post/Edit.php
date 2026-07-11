<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml\Post;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Edit extends Template
{
    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var Post|null
     */
    private $post;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        FormKey $formKey,
        DataPersistorInterface $dataPersistor,
        CategoryCollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->formKey = $formKey;
        $this->dataPersistor = $dataPersistor;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        if ($this->post === null) {
            $postId = (int) $this->getRequest()->getParam('post_id');
            $this->post = $this->postFactory->create();
            if ($postId) {
                $this->post->load($postId);
            }

            $persisted = $this->dataPersistor->get('blogarticle_post');
            if (is_array($persisted) && !empty($persisted)) {
                $this->post->addData($persisted);
                $this->dataPersistor->clear('blogarticle_post');
            }
        }

        return $this->post;
    }

    /**
     * Active categories for the assignment dropdown.
     *
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Category\Collection
     */
    public function getCategoryOptions()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('blogarticle/post/save');
    }

    public function getBackUrl()
    {
        return $this->getUrl('blogarticle/post/index');
    }

    public function getDeleteUrl()
    {
        $post = $this->getPost();
        if (!$post->getId()) {
            return '';
        }

        return $this->getUrl(
            'blogarticle/post/delete',
            [
                'post_id' => (int) $post->getId(),
                'form_key' => $this->getFormKey(),
            ]
        );
    }

    public function isExistingPost()
    {
        return (bool) $this->getPost()->getId();
    }
}
