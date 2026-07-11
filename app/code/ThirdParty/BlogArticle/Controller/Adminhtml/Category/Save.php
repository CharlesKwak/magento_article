<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\CategoryUrlKeyGenerator;

class Save extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::categories';

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CategoryUrlKeyGenerator
     */
    private $urlKeyGenerator;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        DataPersistorInterface $dataPersistor,
        CategoryUrlKeyGenerator $urlKeyGenerator
    ) {
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
        $this->dataPersistor = $dataPersistor;
        $this->urlKeyGenerator = $urlKeyGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : 0;
        $category = $this->categoryFactory->create();
        if ($categoryId) {
            $category->load($categoryId);
            if (!$category->getId()) {
                $this->messageManager->addErrorMessage(__('This category no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $urlKeyInput = isset($data['url_key']) ? trim((string) $data['url_key']) : '';
        $isActive = !empty($data['is_active']) ? 1 : 0;

        if ($name === '') {
            $this->messageManager->addErrorMessage(__('Name is required.'));
            $this->dataPersistor->set('blogarticle_category', $data);
            return $resultRedirect->setPath('*/*/edit', ['category_id' => $categoryId ?: null]);
        }

        $urlSource = $urlKeyInput !== '' ? $urlKeyInput : $name;
        $urlKey = $this->urlKeyGenerator->generate(
            $urlSource,
            $category->getId() ? (int) $category->getId() : null
        );

        $category->setName($name);
        $category->setUrlKey($urlKey);
        $category->setIsActive($isActive);

        try {
            $category->save();
            $this->messageManager->addSuccessMessage(__('The category has been saved.'));
            $this->dataPersistor->clear('blogarticle_category');
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['category_id' => $category->getId()]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
        }

        $this->dataPersistor->set('blogarticle_category', $data);
        return $resultRedirect->setPath('*/*/edit', ['category_id' => $categoryId ?: null]);
    }
}
