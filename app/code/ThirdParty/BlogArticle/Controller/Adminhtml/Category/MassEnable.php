<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory;

class MassEnable extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::categories';

    private Filter $filter;
    private CollectionFactory $collectionFactory;

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
                if (!(int) $category->getIsActive()) {
                    $category->setIsActive(1);
                    $category->save();
                }
                $updated++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($updated) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 category(ies) have been enabled.', $updated)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
