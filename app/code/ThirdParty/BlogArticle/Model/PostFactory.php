<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\ObjectManagerInterface;

class PostFactory
{
    private ObjectManagerInterface $objectManager;
    private string $instanceName;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Post::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return Post
     */
    public function create(array $data = [])
    {
        /** @var Post $post */
        $post = $this->objectManager->create($this->instanceName);
        if (!empty($data)) {
            $post->setData($data);
        }
        return $post;
    }
}
