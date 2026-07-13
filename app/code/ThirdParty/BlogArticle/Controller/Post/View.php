<?php
namespace ThirdParty\BlogArticle\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PostViewCounter;

class View extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var PostFactory
     */
    private $postFactory;
    private $storeManager;
    private $postViewCounter;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        PostFactory $postFactory,
        StoreManagerInterface $storeManager,
        PostViewCounter $postViewCounter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->postFactory = $postFactory;
        $this->storeManager = $storeManager;
        $this->postViewCounter = $postViewCounter;
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

        if (!$post->getId() || !(int) $post->getIsActive()) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $publishedAt = $post->getPublishedAt();
        if ($publishedAt) {
            $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();
            $pub = strtotime((string) $publishedAt . ' UTC');
            if ($pub && $pub > $now) {
                $resultForward = $this->resultForwardFactory->create();
                return $resultForward->forward('noroute');
            }
        }
        $postStore = (int) $post->getStoreId();
        $currentStore = (int) $this->storeManager->getStore()->getId();
        if ($postStore > 0 && $postStore !== $currentStore) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        try {
            $this->postViewCounter->increment((int) $post->getId());
        } catch (\Throwable $e) {
            // never block storefront rendering on counter errors
        }

        $resultPage = $this->resultPageFactory->create();
        $metaTitle = trim((string) $post->getMetaTitle());
        $resultPage->getConfig()->getTitle()->set($metaTitle !== '' ? $metaTitle : $post->getTitle());
        $metaDescription = trim((string) $post->getMetaDescription());
        if ($metaDescription !== '') {
            $resultPage->getConfig()->setDescription($metaDescription);
        }
        return $resultPage;
    }
}
