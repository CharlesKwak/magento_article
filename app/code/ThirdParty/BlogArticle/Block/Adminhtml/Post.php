<?php
namespace ThirdParty\BlogArticle\Block\Adminhtml;

class Post extends \Magento\Backend\Block\Template
{
    /**
     * @var \ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function getPosts()
    {
        return $this->collectionFactory->create();
    }
}
