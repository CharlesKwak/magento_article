<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::tags';

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    public function __construct(Context $context, ForwardFactory $resultForwardFactory)
    {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('edit');
    }
}
