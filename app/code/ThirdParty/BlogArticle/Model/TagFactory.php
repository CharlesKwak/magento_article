<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\ObjectManagerInterface;

class TagFactory
{
    private $objectManager;
    private $instanceName;

    public function __construct(ObjectManagerInterface $objectManager, $instanceName = Tag::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(array $data = [])
    {
        /** @var Tag $tag */
        $tag = $this->objectManager->create($this->instanceName);
        if (!empty($data)) {
            $tag->setData($data);
        }
        return $tag;
    }
}
