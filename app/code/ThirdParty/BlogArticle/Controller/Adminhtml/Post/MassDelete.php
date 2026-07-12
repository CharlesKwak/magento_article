<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    private $filter;
    private $collectionFactory;
    private $postTagLink;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PostTagLink $postTagLink
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->postTagLink = $postTagLink;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection as $post) {
            try {
                $this->postTagLink->setTagsForPost((int) $post->getId(), []);
                $post->delete();
                $deleted++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 post(s) have been deleted.', $deleted));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
