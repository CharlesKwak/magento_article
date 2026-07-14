<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    protected \Magento\Framework\View\Result\PageFactory $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('ThirdParty_BlogArticle::posts');
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Posts'));
        return $resultPage;
    }
}
