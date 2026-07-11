<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::categories';

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var PostCollectionFactory
     */
    private $postCollectionFactory;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        PostCollectionFactory $postCollectionFactory
    ) {
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $categoryId = (int) $this->getRequest()->getParam('category_id');
        if (!$categoryId) {
            $this->messageManager->addErrorMessage(__('We can\'t find a category to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $category = $this->categoryFactory->create()->load($categoryId);
            if (!$category->getId()) {
                $this->messageManager->addErrorMessage(__('This category no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $posts = $this->postCollectionFactory->create();
            $posts->addFieldToFilter('category_id', $categoryId);
            if ($posts->getSize() > 0) {
                $this->messageManager->addErrorMessage(
                    __('Cannot delete category with assigned posts. Reassign or clear posts first.')
                );
                return $resultRedirect->setPath('*/*/edit', ['category_id' => $categoryId]);
            }

            $category->delete();
            $this->messageManager->addSuccessMessage(__('The category has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['category_id' => $categoryId]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
