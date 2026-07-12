<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::comments';

    private $commentRepository;

    public function __construct(Context $context, CommentRepositoryInterface $commentRepository)
    {
        parent::__construct($context);
        $this->commentRepository = $commentRepository;
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('comment_id');
        try {
            $this->commentRepository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('Comment deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
