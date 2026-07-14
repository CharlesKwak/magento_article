<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Tag;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ThirdParty\BlogArticle\Model\TagFactory;
use ThirdParty\BlogArticle\Model\PostTagLink;

class Delete extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::tags';

    private TagFactory $tagFactory;
    private PostTagLink $postTagLink;

    public function __construct(
        Context $context,
        TagFactory $tagFactory,
        PostTagLink $postTagLink
    ) {
        parent::__construct($context);
        $this->tagFactory = $tagFactory;
        $this->postTagLink = $postTagLink;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $tagId = (int) $this->getRequest()->getParam('tag_id');
        if (!$tagId) {
            $this->messageManager->addErrorMessage(__('We can\'t find a tag to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $tag = $this->tagFactory->create()->load($tagId);
            if (!$tag->getId()) {
                $this->messageManager->addErrorMessage(__('This tag no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if ($this->postTagLink->countPostsForTag($tagId) > 0) {
                $this->messageManager->addErrorMessage(
                    __('Cannot delete tag with assigned posts. Remove tag from posts first.')
                );
                return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId]);
            }

            $tag->delete();
            $this->messageManager->addSuccessMessage(__('The tag has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
