<?php
namespace ThirdParty\BlogArticle\Block;

class Article extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
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
