<?php
namespace ThirdParty\BlogArticle\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\CommentRepositoryInterface;
use ThirdParty\BlogArticle\Api\Data\CommentInterface;
use ThirdParty\BlogArticle\Api\Data\CommentInterfaceFactory;
use ThirdParty\BlogArticle\Api\PostRepositoryInterface;
use ThirdParty\BlogArticle\Model\ResourceModel\Comment\CollectionFactory;

class CommentRepository implements CommentRepositoryInterface
{
    private $commentFactory;
    private $collectionFactory;
    private $dataFactory;
    private $postRepository;
    private $config;
    private $notifier;

    public function __construct(
        CommentFactory $commentFactory,
        CollectionFactory $collectionFactory,
        CommentInterfaceFactory $dataFactory,
        PostRepositoryInterface $postRepository,
        Config $config,
        CommentNotifier $notifier
    ) {
        $this->commentFactory = $commentFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataFactory = $dataFactory;
        $this->postRepository = $postRepository;
        $this->config = $config;
        $this->notifier = $notifier;
    }

    public function getById($commentId)
    {
        $model = $this->commentFactory->create()->load((int) $commentId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Comment with ID "%1" does not exist.', $commentId));
        }
        return $this->toDataModel($model);
    }

    public function getListByPostId($postId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('post_id', (int) $postId);
        $collection->addFieldToFilter('is_approved', 1);
        $collection->setOrder('creation_time', 'ASC');
        $items = [];
        foreach ($collection as $comment) {
            $items[] = $this->toDataModel($comment);
        }
        return $items;
    }

    public function submit(CommentInterface $comment)
    {
        if (!$this->config->isCommentsEnabled()) {
            throw new LocalizedException(__('Comments are disabled.'));
        }
        $postId = (int) $comment->getPostId();
        if ($postId <= 0) {
            throw new LocalizedException(__('post_id is required.'));
        }
        $this->postRepository->getById($postId, true);

        $author = trim((string) $comment->getAuthorName());
        $content = trim((string) $comment->getContent());
        if ($author === '' || $content === '') {
            throw new LocalizedException(__('Author name and content are required.'));
        }
        if (mb_strlen($content) > 5000) {
            throw new LocalizedException(__('Comment is too long.'));
        }

        $parentId = $comment->getParentId() ? (int) $comment->getParentId() : null;
        if ($parentId) {
            $parent = $this->commentFactory->create()->load($parentId);
            if (!$parent->getId()
                || (int) $parent->getPostId() !== $postId
                || !(int) $parent->getIsApproved()
            ) {
                throw new LocalizedException(__('Invalid parent comment.'));
            }
            // Only one level of nesting: replies to replies attach to root parent
            if ($parent->getParentId()) {
                $parentId = (int) $parent->getParentId();
            }
        }

        $model = $this->commentFactory->create();
        $model->setPostId($postId);
        $model->setParentId($parentId);
        $model->setAuthorName($author);
        $model->setAuthorEmail($comment->getAuthorEmail() ? trim((string) $comment->getAuthorEmail()) : null);
        $model->setContent($content);
        $model->setIsApproved($this->config->isCommentsAutoApprove() ? 1 : 0);

        try {
            $model->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save comment: %1', $e->getMessage()), $e);
        }

        $data = $this->toDataModel($model);
        $this->notifier->notify($data);
        return $data;
    }

    public function save(CommentInterface $comment)
    {
        $model = $this->commentFactory->create();
        if ($comment->getCommentId()) {
            $model->load((int) $comment->getCommentId());
            if (!$model->getId()) {
                throw new NoSuchEntityException(__('Comment with ID "%1" does not exist.', $comment->getCommentId()));
            }
        }
        if ($comment->getPostId()) {
            $model->setPostId((int) $comment->getPostId());
        }
        if ($comment->getParentId() !== null) {
            $model->setParentId($comment->getParentId() ?: null);
        }
        if ($comment->getAuthorName() !== null) {
            $model->setAuthorName(trim((string) $comment->getAuthorName()));
        }
        if ($comment->getAuthorEmail() !== null) {
            $model->setAuthorEmail(trim((string) $comment->getAuthorEmail()) ?: null);
        }
        if ($comment->getContent() !== null) {
            $model->setContent(trim((string) $comment->getContent()));
        }
        if ($comment->getIsApproved() !== null) {
            $model->setIsApproved((int) $comment->getIsApproved() ? 1 : 0);
        }
        try {
            $model->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save comment: %1', $e->getMessage()), $e);
        }
        return $this->toDataModel($model);
    }

    public function deleteById($commentId)
    {
        $model = $this->commentFactory->create()->load((int) $commentId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Comment with ID "%1" does not exist.', $commentId));
        }
        try {
            // Orphan replies: clear parent_id of children
            $children = $this->collectionFactory->create();
            $children->addFieldToFilter('parent_id', (int) $commentId);
            foreach ($children as $child) {
                $child->setParentId(null);
                $child->save();
            }
            $model->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete comment: %1', $e->getMessage()), $e);
        }
        return true;
    }

    public function approve($commentId)
    {
        $model = $this->commentFactory->create()->load((int) $commentId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Comment with ID "%1" does not exist.', $commentId));
        }
        $model->setIsApproved(1);
        $model->save();
        return $this->toDataModel($model);
    }

    private function toDataModel(Comment $comment): CommentInterface
    {
        /** @var CommentInterface $data */
        $data = $this->dataFactory->create();
        $data->setCommentId((int) $comment->getId());
        $data->setPostId((int) $comment->getPostId());
        $data->setParentId($comment->getParentId() ? (int) $comment->getParentId() : null);
        $data->setAuthorName((string) $comment->getAuthorName());
        $data->setAuthorEmail($comment->getAuthorEmail() ? (string) $comment->getAuthorEmail() : null);
        $data->setContent((string) $comment->getContent());
        $data->setIsApproved((int) $comment->getIsApproved());
        $data->setCreationTime((string) $comment->getCreationTime());
        return $data;
    }
}
