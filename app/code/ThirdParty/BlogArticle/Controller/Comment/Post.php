<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Comment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Api\Data\CommentInterfaceFactory;
use ThirdParty\BlogArticle\Model\CommentSpamGuard;
use ThirdParty\BlogArticle\Model\RecaptchaValidator;

class Post extends Action implements HttpPostActionInterface
{
    private FormKeyValidator $formKeyValidator;
    private CommentRepositoryInterface $commentRepository;
    private CommentInterfaceFactory $commentFactory;
    private CommentSpamGuard $spamGuard;
    private RecaptchaValidator $recaptchaValidator;

    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        CommentRepositoryInterface $commentRepository,
        CommentInterfaceFactory $commentFactory,
        CommentSpamGuard $spamGuard,
        RecaptchaValidator $recaptchaValidator
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->spamGuard = $spamGuard;
        $this->recaptchaValidator = $recaptchaValidator;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $postId = (int) $this->getRequest()->getParam('post_id');
        $urlKey = (string) $this->getRequest()->getParam('url_key', '');
        $backUrl = $urlKey !== ''
            ? $this->_url->getUrl('', ['_direct' => 'blog/' . $urlKey])
            : $this->_url->getUrl('blog/post/view', ['id' => $postId]);

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setUrl($backUrl);
        }

        try {
            $params = $this->getRequest()->getParams();
            $this->spamGuard->assertNotSpam($params);
            $this->recaptchaValidator->assertValid(
                $this->getRequest()->getParam('g-recaptcha-response')
            );

            /** @var \ThirdParty\BlogArticle\Api\Data\CommentInterface $comment */
            $comment = $this->commentFactory->create();
            $comment->setPostId($postId);
            $parentId = (int) $this->getRequest()->getParam('parent_id', 0);
            if ($parentId > 0) {
                $comment->setParentId($parentId);
            }
            $comment->setAuthorName((string) $this->getRequest()->getParam('author_name', ''));
            $comment->setAuthorEmail((string) $this->getRequest()->getParam('author_email', ''));
            $comment->setContent((string) $this->getRequest()->getParam('content', ''));
            $saved = $this->commentRepository->submit($comment);
            if ((int) $saved->getIsApproved() === 1) {
                $this->messageManager->addSuccessMessage(__('Your comment has been published.'));
            } else {
                $this->messageManager->addSuccessMessage(__('Thank you! Your comment is awaiting moderation.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to submit comment.'));
        }

        return $resultRedirect->setUrl($backUrl . '#comments');
    }
}
