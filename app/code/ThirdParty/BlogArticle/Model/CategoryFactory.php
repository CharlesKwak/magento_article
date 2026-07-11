<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\ObjectManagerInterface;

class CategoryFactory
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
        $instanceName = Category::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return Category
     */
    public function create(array $data = [])
    {
        /** @var Category $category */
        $category = $this->objectManager->create($this->instanceName);
        if (!empty($data)) {
            $category->setData($data);
        }
        return $category;
    }
}
