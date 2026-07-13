<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory;

class Tag extends Template
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
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Tag\Collection
     */
    public function getTags()
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getNewUrl()
    {
        return $this->getUrl('blogarticle/tag/new');
    }

    public function getEditUrl($tagId)
    {
        return $this->getUrl('blogarticle/tag/edit', ['tag_id' => (int) $tagId]);
    }

    public function getDeleteUrl($tagId)
    {
        return $this->getUrl(
            'blogarticle/tag/delete',
            ['tag_id' => (int) $tagId, 'form_key' => $this->getFormKey()]
        );
    }
}
