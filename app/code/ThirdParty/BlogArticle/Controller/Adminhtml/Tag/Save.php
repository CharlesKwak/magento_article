<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Controller\Adminhtml\Tag;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use ThirdParty\BlogArticle\Model\TagFactory;
use ThirdParty\BlogArticle\Model\TagUrlKeyGenerator;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::tags';

    private TagFactory $tagFactory;
    private DataPersistorInterface $dataPersistor;
    private TagUrlKeyGenerator $urlKeyGenerator;

    public function __construct(
        Context $context,
        TagFactory $tagFactory,
        DataPersistorInterface $dataPersistor,
        TagUrlKeyGenerator $urlKeyGenerator
    ) {
        parent::__construct($context);
        $this->tagFactory = $tagFactory;
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

        $tagId = isset($data['tag_id']) ? (int) $data['tag_id'] : 0;
        $tag = $this->tagFactory->create();
        if ($tagId) {
            $tag->load($tagId);
            if (!$tag->getId()) {
                $this->messageManager->addErrorMessage(__('This tag no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $urlKeyInput = isset($data['url_key']) ? trim((string) $data['url_key']) : '';
        $description = isset($data['description']) ? trim((string) $data['description']) : '';
        $isActive = !empty($data['is_active']) ? 1 : 0;

        if ($name === '') {
            $this->messageManager->addErrorMessage(__('Name is required.'));
            $this->dataPersistor->set('blogarticle_tag', $data);
            return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId ?: null]);
        }

        $urlSource = $urlKeyInput !== '' ? $urlKeyInput : $name;
        $urlKey = $this->urlKeyGenerator->generate(
            $urlSource,
            $tag->getId() ? (int) $tag->getId() : null
        );

        $tag->setName($name);
        $tag->setUrlKey($urlKey);
        $tag->setData('description', $description !== '' ? $description : null);
        $tag->setIsActive($isActive);

        try {
            $tag->save();
            $this->messageManager->addSuccessMessage(__('The tag has been saved.'));
            $this->dataPersistor->clear('blogarticle_tag');
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tag->getId()]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the tag.'));
        }

        $this->dataPersistor->set('blogarticle_tag', $data);
        return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId ?: null]);
    }
}
