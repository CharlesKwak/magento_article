<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Post;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PostViewCounter;
use ThirdParty\BlogArticle\Model\PostVisibility;

class View extends Action implements HttpGetActionInterface
{
    private PageFactory $resultPageFactory;
    private ForwardFactory $resultForwardFactory;
    private PostFactory $postFactory;
    private PostViewCounter $postViewCounter;
    private PostVisibility $postVisibility;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        PostFactory $postFactory,
        PostViewCounter $postViewCounter,
        PostVisibility $postVisibility
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->postFactory = $postFactory;
        $this->postViewCounter = $postViewCounter;
        $this->postVisibility = $postVisibility;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $post = $this->postFactory->create();
        $urlKey = (string) $this->getRequest()->getParam('url_key', '');
        $id = (int) $this->getRequest()->getParam('id', 0);

        if ($urlKey !== '') {
            $post->load($urlKey, 'url_key');
        } elseif ($id) {
            $post->load($id);
        }

        $preview = trim((string) $this->getRequest()->getParam('preview', ''));
        $allowPreview = $this->postVisibility->isPreviewAllowed($post, $preview);

        if (!$this->postVisibility->isVisible($post, $allowPreview)) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        // Do not count draft/scheduled previews toward view_count.
        if (!$allowPreview) {
            try {
                $this->postViewCounter->increment((int) $post->getId());
            } catch (\Throwable $e) {
                // never block storefront rendering on counter errors
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $metaTitle = trim((string) $post->getMetaTitle());
        $resultPage->getConfig()->getTitle()->set($metaTitle !== '' ? $metaTitle : $post->getTitle());
        $metaDescription = trim((string) $post->getMetaDescription());
        if ($metaDescription !== '') {
            $resultPage->getConfig()->setDescription($metaDescription);
        }

        // Distraction-free reading mode: 1-column layout, no sidebar chrome.
        if ($this->getRequest()->getParam('reading')) {
            $resultPage->getConfig()->setPageLayout('1column');
            $resultPage->addHandle('blog_post_view_reading');
        }

        return $resultPage;
    }
}
