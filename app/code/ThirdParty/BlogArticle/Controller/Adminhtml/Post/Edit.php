<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use ThirdParty\BlogArticle\Model\PostFactory;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var PostFactory
     */
    private $postFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PostFactory $postFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->postFactory = $postFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $postId = (int) $this->getRequest()->getParam('post_id');
        $post = $this->postFactory->create();

        if ($postId) {
            $post->load($postId);
            if (!$post->getId()) {
                $this->messageManager->addErrorMessage(__('This post no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('ThirdParty_BlogArticle::posts');
        $resultPage->getConfig()->getTitle()->prepend(
            $post->getId() ? __('Edit Blog Post') : __('New Blog Post')
        );

        return $resultPage;
    }
}
