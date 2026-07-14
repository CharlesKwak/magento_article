<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\ResourceModel\Category;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    private ObjectManagerInterface $objectManager;
    private string $instanceName;

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
