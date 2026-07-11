<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml\Category;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey;
use ThirdParty\BlogArticle\Model\Category;
use ThirdParty\BlogArticle\Model\CategoryFactory;

class Edit extends Template
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Category|null
     */
    private $category;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        FormKey $formKey,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->formKey = $formKey;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $data);
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        if ($this->category === null) {
            $categoryId = (int) $this->getRequest()->getParam('category_id');
            $this->category = $this->categoryFactory->create();
            if ($categoryId) {
                $this->category->load($categoryId);
            }
            $persisted = $this->dataPersistor->get('blogarticle_category');
            if (is_array($persisted) && !empty($persisted)) {
                $this->category->addData($persisted);
                $this->dataPersistor->clear('blogarticle_category');
            }
        }
        return $this->category;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('blogarticle/category/save');
    }

    public function getBackUrl()
    {
        return $this->getUrl('blogarticle/category/index');
    }

    public function getDeleteUrl()
    {
        $category = $this->getCategory();
        if (!$category->getId()) {
            return '';
        }
        return $this->getUrl(
            'blogarticle/category/delete',
            ['category_id' => (int) $category->getId(), 'form_key' => $this->getFormKey()]
        );
    }

    public function isExisting()
    {
        return (bool) $this->getCategory()->getId();
    }
}
