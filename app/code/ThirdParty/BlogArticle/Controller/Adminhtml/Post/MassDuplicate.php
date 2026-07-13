<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Model\PostDuplicator;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class MassDuplicate extends Action
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';
    private const MAX_ITEMS = 20;

    private $filter;
    private $collectionFactory;
    private $postDuplicator;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PostDuplicator $postDuplicator
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->postDuplicator = $postDuplicator;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $created = 0;
        $skipped = 0;
        $count = 0;
        foreach ($collection as $post) {
            $count++;
            if ($count > self::MAX_ITEMS) {
                $skipped = (int) $collection->getSize() - self::MAX_ITEMS;
                break;
            }
            try {
                $this->postDuplicator->duplicate((int) $post->getId(), false);
                $created++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($created) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 post(s) have been duplicated as disabled drafts.', $created)
            );
        }
        if ($skipped > 0) {
            $this->messageManager->addNoticeMessage(
                __('Mass duplicate is limited to %1 posts per run. %2 selected post(s) were skipped.', self::MAX_ITEMS, $skipped)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
