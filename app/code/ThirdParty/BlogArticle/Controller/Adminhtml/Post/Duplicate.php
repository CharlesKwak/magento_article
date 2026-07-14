<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Model\PostDuplicator;

class Duplicate extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    private PostDuplicator $postDuplicator;

    public function __construct(
        Context $context,
        PostDuplicator $postDuplicator
    ) {
        parent::__construct($context);
        $this->postDuplicator = $postDuplicator;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $postId = (int) $this->getRequest()->getParam('post_id');
        if ($postId <= 0) {
            $this->messageManager->addErrorMessage(__('We can\'t find a post to duplicate.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $copy = $this->postDuplicator->duplicate($postId, false);
            $this->messageManager->addSuccessMessage(
                __('The post has been duplicated as a disabled draft (ID %1).', (int) $copy->getId())
            );
            return $resultRedirect->setPath('*/*/edit', ['post_id' => (int) $copy->getId()]);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while duplicating the post.'));
        }

        return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId]);
    }
}
