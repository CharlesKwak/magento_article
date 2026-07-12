<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;

class MassEnable extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::tags';

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
        foreach ($collection as $tag) {
            try {
                if (!(int) $tag->getIsActive()) {
                    $tag->setIsActive(1);
                    $tag->save();
                }
                $updated++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($updated) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 tag(s) have been enabled.', $updated)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
