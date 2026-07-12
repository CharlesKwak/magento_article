<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\ObjectManagerInterface;

class CommentFactory
{
    private $objectManager;
    private $instanceName;

    public function __construct(ObjectManagerInterface $objectManager, $instanceName = Comment::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(array $data = [])
    {
        /** @var Comment $comment */
        $comment = $this->objectManager->create($this->instanceName);
        if ($data) {
            $comment->setData($data);
        }
        return $comment;
    }
}
