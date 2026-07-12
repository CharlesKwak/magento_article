<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class MassEnable extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

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
        foreach ($collection as $post) {
            try {
                if (!(int) $post->getIsActive()) {
                    $post->setIsActive(1);
                    $post->save();
                }
                $updated++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($updated) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 post(s) have been enabled.', $updated)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
