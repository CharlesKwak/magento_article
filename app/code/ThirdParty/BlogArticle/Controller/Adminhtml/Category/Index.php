<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::categories';

    private PageFactory $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('ThirdParty_BlogArticle::categories');
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Categories'));
        return $resultPage;
    }
}
