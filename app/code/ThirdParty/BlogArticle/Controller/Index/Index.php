<?php
namespace ThirdParty\BlogArticle\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\TagFactory;

class Index extends Action
{
    private $resultPageFactory;
    private $categoryFactory;
    private $tagFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $title = (string) __('Blog');

        $catKey = trim((string) $this->getRequest()->getParam('cat', ''));
        if ($catKey !== '') {
            $category = $this->categoryFactory->create()->load($catKey, 'url_key');
            if ($category->getId() && (int) $category->getIsActive()) {
                $title = (string) __('Blog — %1', $category->getName());
            }
        } else {
            $tagKey = trim((string) $this->getRequest()->getParam('tag', ''));
            if ($tagKey !== '') {
                $tag = $this->tagFactory->create()->load($tagKey, 'url_key');
                if ($tag->getId() && (int) $tag->getIsActive()) {
                    $title = (string) __('Blog — #%1', $tag->getName());
                }
            }
        }

        $resultPage->getConfig()->getTitle()->set($title);
        return $resultPage;
    }
}
