<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Api\TagRepositoryInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::tags';

    private $filter;
    private $collectionFactory;
    private $tagRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        TagRepositoryInterface $tagRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->tagRepository = $tagRepository;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        $skipped = 0;
        foreach ($collection as $tag) {
            try {
                $this->tagRepository->deleteById((int) $tag->getId());
                $deleted++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }
        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 tag(s) have been deleted.', $deleted));
        }
        if ($skipped) {
            $this->messageManager->addNoticeMessage(__('%1 tag(s) could not be deleted (possibly still linked to posts).', $skipped));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
