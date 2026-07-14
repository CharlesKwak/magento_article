<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ThirdParty\BlogArticle\Model\PostFactory;

class Delete extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    private PostFactory $postFactory;

    public function __construct(
        Context $context,
        PostFactory $postFactory
    ) {
        parent::__construct($context);
        $this->postFactory = $postFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $postId = (int) $this->getRequest()->getParam('post_id');

        if (!$postId) {
            $this->messageManager->addErrorMessage(__('We can\'t find a post to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $post = $this->postFactory->create()->load($postId);
            if (!$post->getId()) {
                $this->messageManager->addErrorMessage(__('This post no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $post->delete();
            $this->messageManager->addSuccessMessage(__('The blog post has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
