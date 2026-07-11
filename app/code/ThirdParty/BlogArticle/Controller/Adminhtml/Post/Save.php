<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use ThirdParty\BlogArticle\Model\PostFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->postFactory = $postFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $postId = isset($data['post_id']) ? (int) $data['post_id'] : 0;
        $post = $this->postFactory->create();

        if ($postId) {
            $post->load($postId);
            if (!$post->getId()) {
                $this->messageManager->addErrorMessage(__('This post no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        $content = isset($data['content']) ? trim((string) $data['content']) : '';

        if ($title === '' || $content === '') {
            $this->messageManager->addErrorMessage(__('Title and content are required.'));
            $this->dataPersistor->set('blogarticle_post', $data);
            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
        }

        $post->setTitle($title);
        $post->setContent($content);

        try {
            $post->save();
            $this->messageManager->addSuccessMessage(__('The blog post has been saved.'));
            $this->dataPersistor->clear('blogarticle_post');

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['post_id' => $post->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the post.'));
        }

        $this->dataPersistor->set('blogarticle_post', $data);
        return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
    }
}
