<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory;

class MassDisable extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::categories';

    private $filter;
    private $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $updated = 0;
        foreach ($collection as $category) {
            try {
                if ((int) $category->getIsActive()) {
                    $category->setIsActive(0);
                    $category->save();
                }
                $updated++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($updated) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 category(ies) have been disabled.', $updated)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
