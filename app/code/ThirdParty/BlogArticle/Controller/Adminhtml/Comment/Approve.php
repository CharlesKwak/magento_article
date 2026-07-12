<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;

class Approve extends Action
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
            $this->commentRepository->approve($id);
            $this->messageManager->addSuccessMessage(__('Comment approved.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
