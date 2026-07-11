<?php
namespace ThirdParty\BlogArticle\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use ThirdParty\BlogArticle\Model\FeaturedImageUploader;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\UrlKeyGenerator;

class Save extends Action
{
    const ADMIN_RESOURCE = 'ThirdParty_BlogArticle::posts';

    private $postFactory;
    private $dataPersistor;
    private $urlKeyGenerator;
    private $postTagLink;
    private $imageUploader;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        DataPersistorInterface $dataPersistor,
        UrlKeyGenerator $urlKeyGenerator,
        PostTagLink $postTagLink,
        FeaturedImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->postFactory = $postFactory;
        $this->dataPersistor = $dataPersistor;
        $this->urlKeyGenerator = $urlKeyGenerator;
        $this->postTagLink = $postTagLink;
        $this->imageUploader = $imageUploader;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $postId = isset($data['post_id']) ? (int) $data['post_id'] : 0;
        $post = $this->postFactory->create();

        if ($postId) {
            $post->load($postId);
            if (!$post->getId()) {
                $this->messageManager->addErrorMessage(__('This post no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        $content = isset($data['content']) ? trim((string) $data['content']) : '';
        $urlKeyInput = isset($data['url_key']) ? trim((string) $data['url_key']) : '';
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $categoryId = isset($data['category_id']) && $data['category_id'] !== ''
            ? (int) $data['category_id']
            : null;
        $tagIds = isset($data['tag_ids']) && is_array($data['tag_ids'])
            ? array_map('intval', $data['tag_ids'])
            : [];
        $featuredImage = isset($data['featured_image']) ? trim((string) $data['featured_image']) : '';
        $metaTitle = isset($data['meta_title']) ? trim((string) $data['meta_title']) : '';
        $metaDescription = isset($data['meta_description']) ? trim((string) $data['meta_description']) : '';
        $excerpt = isset($data['excerpt']) ? trim((string) $data['excerpt']) : '';
        $publishedAt = isset($data['published_at']) ? trim((string) $data['published_at']) : '';

        if ($title === '' || $content === '') {
            $this->messageManager->addErrorMessage(__('Title and content are required.'));
            $this->dataPersistor->set('blogarticle_post', $data);
            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
        }

        try {
            $uploaded = $this->imageUploader->upload('featured_image_file');
            if ($uploaded) {
                $featuredImage = $uploaded;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('blogarticle_post', $data);
            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
        }

        $urlSource = $urlKeyInput !== '' ? $urlKeyInput : $title;
        $urlKey = $this->urlKeyGenerator->generate(
            $urlSource,
            $post->getId() ? (int) $post->getId() : null
        );

        $post->setTitle($title);
        $post->setContent($content);
        $post->setUrlKey($urlKey);
        $post->setExcerpt($excerpt !== '' ? $excerpt : null);
        $post->setFeaturedImage($featuredImage !== '' ? $featuredImage : null);
        $post->setMetaTitle($metaTitle !== '' ? $metaTitle : null);
        $post->setMetaDescription($metaDescription !== '' ? $metaDescription : null);
        $post->setPublishedAt($publishedAt !== '' ? $publishedAt : null);
        $post->setIsActive($isActive);
        $post->setCategoryId($categoryId);

        try {
            $post->save();
            $this->postTagLink->setTagsForPost((int) $post->getId(), $tagIds);
            $this->messageManager->addSuccessMessage(__('The blog post has been saved.'));
            $this->dataPersistor->clear('blogarticle_post');

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['post_id' => $post->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the post.'));
        }

        $this->dataPersistor->set('blogarticle_post', $data);
        return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
    }
}
