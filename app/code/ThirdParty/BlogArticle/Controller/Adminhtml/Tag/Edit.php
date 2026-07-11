<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use ThirdParty\BlogArticle\Model\TagFactory;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::tags';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TagFactory $tagFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->tagFactory = $tagFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $tagId = (int) $this->getRequest()->getParam('tag_id');
        $tag = $this->tagFactory->create();

        if ($tagId) {
            $tag->load($tagId);
            if (!$tag->getId()) {
                $this->messageManager->addErrorMessage(__('This tag no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('ThirdParty_BlogArticle::tags');
        $resultPage->getConfig()->getTitle()->prepend(
            $tag->getId() ? __('Edit Tag') : __('New Tag')
        );
        return $resultPage;
    }
}
