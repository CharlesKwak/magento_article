<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml\Tag;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey;
use ThirdParty\BlogArticle\Model\Tag;
use ThirdParty\BlogArticle\Model\TagFactory;

class Edit extends Template
{
    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Tag|null
     */
    private $tag;

    public function __construct(
        Context $context,
        TagFactory $tagFactory,
        FormKey $formKey,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        $this->tagFactory = $tagFactory;
        $this->formKey = $formKey;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $data);
    }

    /**
     * @return Tag
     */
    public function getTag()
    {
        if ($this->tag === null) {
            $tagId = (int) $this->getRequest()->getParam('tag_id');
            $this->tag = $this->tagFactory->create();
            if ($tagId) {
                $this->tag->load($tagId);
            }
            $persisted = $this->dataPersistor->get('blogarticle_tag');
            if (is_array($persisted) && !empty($persisted)) {
                $this->tag->addData($persisted);
                $this->dataPersistor->clear('blogarticle_tag');
            }
        }
        return $this->tag;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('blogarticle/tag/save');
    }

    public function getBackUrl()
    {
        return $this->getUrl('blogarticle/tag/index');
    }

    public function getDeleteUrl()
    {
        $tag = $this->getTag();
        if (!$tag->getId()) {
            return '';
        }
        return $this->getUrl(
            'blogarticle/tag/delete',
            ['tag_id' => (int) $tag->getId(), 'form_key' => $this->getFormKey()]
        );
    }

    public function isExisting()
    {
        return (bool) $this->getTag()->getId();
    }
}
