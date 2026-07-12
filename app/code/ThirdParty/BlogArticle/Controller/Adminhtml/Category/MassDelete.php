<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Api\CategoryRepositoryInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::categories';

    private $filter;
    private $collectionFactory;
    private $categoryRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        $skipped = 0;
        foreach ($collection as $category) {
            try {
                $this->categoryRepository->deleteById((int) $category->getId());
                $deleted++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }
        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 category(ies) have been deleted.', $deleted));
        }
        if ($skipped) {
            $this->messageManager->addNoticeMessage(__('%1 category(ies) could not be deleted (possibly still assigned to posts).', $skipped));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
