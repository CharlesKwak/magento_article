<?php
namespace ThirdParty\BlogArticle\Model\ResourceModel\Tag;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    private $objectManager;
    private $instanceName;

    public function __construct(ObjectManagerInterface $objectManager, $instanceName = Collection::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
