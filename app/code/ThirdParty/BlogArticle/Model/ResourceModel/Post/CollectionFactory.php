<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\ResourceModel\Post;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    protected ObjectManagerInterface $objectManager;
    protected string $instanceName;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
