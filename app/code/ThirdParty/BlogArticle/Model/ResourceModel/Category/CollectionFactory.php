<?php
namespace ThirdParty\BlogArticle\Model\ResourceModel\Category;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return Collection
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
