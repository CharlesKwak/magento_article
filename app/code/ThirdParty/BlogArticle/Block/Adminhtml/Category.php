<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory;

class Category extends Template
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FormKey
     */
    protected $formKey;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Category\Collection
     */
    public function getCategories()
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder('sort_order', 'ASC');
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getNewUrl()
    {
        return $this->getUrl('blogarticle/category/new');
    }

    public function getEditUrl($categoryId)
    {
        return $this->getUrl('blogarticle/category/edit', ['category_id' => (int) $categoryId]);
    }

    public function getDeleteUrl($categoryId)
    {
        return $this->getUrl(
            'blogarticle/category/delete',
            ['category_id' => (int) $categoryId, 'form_key' => $this->getFormKey()]
        );
    }
}
