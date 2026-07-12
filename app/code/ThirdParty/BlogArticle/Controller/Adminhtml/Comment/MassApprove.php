<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Comment\CollectionFactory;

class MassApprove extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::comments';

    private $filter;
    private $collectionFactory;
    private $commentRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CommentRepositoryInterface $commentRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->commentRepository = $commentRepository;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count = 0;
        foreach ($collection as $item) {
            $this->commentRepository->approve((int) $item->getId());
            $count++;
        }
        $this->messageManager->addSuccessMessage(__('Approved %1 comment(s).', $count));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
