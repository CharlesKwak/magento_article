<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml\Post;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFactory;

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
     * @var Post|null
     */
    private $post;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        FormKey $formKey,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->formKey = $formKey;
        $this->dataPersistor = $dataPersistor;
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
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('blogarticle/post/save');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('blogarticle/post/index');
    }

    /**
     * @return string
     */
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

    /**
     * @return bool
     */
    public function isExistingPost()
    {
        return (bool) $this->getPost()->getId();
    }
}
