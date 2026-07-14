<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    private Filter $filter;
    private CollectionFactory $collectionFactory;
    private PostTagLink $postTagLink;

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
