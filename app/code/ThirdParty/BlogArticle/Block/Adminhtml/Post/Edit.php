<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Block\Adminhtml\Post;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\System\Store as SystemStore;
use ThirdParty\BlogArticle\Model\Post;
use ThirdParty\BlogArticle\Model\PostFactory;
use ThirdParty\BlogArticle\Model\PostProductLink;
use ThirdParty\BlogArticle\Model\PostTagLink;
use ThirdParty\BlogArticle\Model\PostVisibility;
use ThirdParty\BlogArticle\Model\PreviewToken;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

class Edit extends Template
{
    private PostFactory $postFactory;
    protected $formKey;
    private DataPersistorInterface $dataPersistor;
    private CategoryCollectionFactory $categoryCollectionFactory;
    private TagCollectionFactory $tagCollectionFactory;
    private PostTagLink $postTagLink;
    private PostProductLink $postProductLink;
    private SystemStore $systemStore;
    private WysiwygConfig $wysiwygConfig;
    private Json $json;
    private PreviewToken $previewToken;
    private PostVisibility $postVisibility;
    private ?Post $post = null;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        FormKey $formKey,
        DataPersistorInterface $dataPersistor,
        CategoryCollectionFactory $categoryCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        PostTagLink $postTagLink,
        PostProductLink $postProductLink,
        SystemStore $systemStore,
        WysiwygConfig $wysiwygConfig,
        Json $json,
        PreviewToken $previewToken,
        PostVisibility $postVisibility,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->formKey = $formKey;
        $this->dataPersistor = $dataPersistor;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->postTagLink = $postTagLink;
        $this->postProductLink = $postProductLink;
        $this->systemStore = $systemStore;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->json = $json;
        $this->previewToken = $previewToken;
        $this->postVisibility = $postVisibility;
        parent::__construct($context, $data);
    }

    public function getPost()
    {
        if ($this->post === null) {
            $postId = (int) $this->getRequest()->getParam('post_id');
            $this->post = $this->postFactory->create();
            if ($postId) {
                $this->post->load($postId);
            }

            $persisted = $this->dataPersistor->get('blogarticle_post');
            if (is_array($persisted) && !empty($persisted)) {
                $this->post->addData($persisted);
                $this->dataPersistor->clear('blogarticle_post');
            }
        }

        return $this->post;
    }

    public function getCategoryOptions()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('sort_order', 'ASC');
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    public function getTagOptions()
    {
        $collection = $this->tagCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    /**
     * Store view options: 0 = all stores.
     *
     * @return array [storeId => label]
     */
    public function getStoreOptions(): array
    {
        $options = [0 => (string) __('All Store Views')];
        foreach ($this->systemStore->getStoreValuesForForm(false, false) as $group) {
            if (!empty($group['value']) && is_array($group['value'])) {
                foreach ($group['value'] as $store) {
                    if (isset($store['value'], $store['label'])) {
                        $options[(int) $store['value']] = (string) $store['label'];
                    }
                }
            }
        }
        return $options;
    }

    /**
     * @return int[]
     */
    public function getSelectedTagIds(): array
    {
        $post = $this->getPost();
        $persisted = $post->getData('tag_ids');
        if (is_array($persisted)) {
            return array_map('intval', $persisted);
        }
        if ($post->getId()) {
            return $this->postTagLink->getTagIdsForPost((int) $post->getId());
        }
        return [];
    }

    /**
     * Comma-separated product SKUs (or IDs) for the admin form.
     */
    public function getProductSkusInput(): string
    {
        $post = $this->getPost();
        $persisted = $post->getData('product_skus');
        if (is_string($persisted) && $persisted !== '') {
            return $persisted;
        }
        if ($post->getId()) {
            return implode(', ', $this->postProductLink->getProductSkusForPost((int) $post->getId()));
        }
        return '';
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('blogarticle/post/save');
    }

    public function getBackUrl()
    {
        return $this->getUrl('blogarticle/post/index');
    }

    public function getDeleteUrl()
    {
        $post = $this->getPost();
        if (!$post->getId()) {
            return '';
        }

        return $this->getUrl(
            'blogarticle/post/delete',
            [
                'post_id' => (int) $post->getId(),
                'form_key' => $this->getFormKey(),
            ]
        );
    }

    public function getDuplicateUrl(): string
    {
        $post = $this->getPost();
        if (!$post->getId()) {
            return '';
        }
        return $this->getUrl(
            'blogarticle/post/duplicate',
            [
                'post_id' => (int) $post->getId(),
                'form_key' => $this->getFormKey(),
            ]
        );
    }

    public function isExistingPost()
    {
        return (bool) $this->getPost()->getId();
    }

    /**
     * TinyMCE / Magento WYSIWYG config JSON for the content field.
     */
    public function getWysiwygConfigJson(): string
    {
        $config = $this->wysiwygConfig->getConfig([
            'add_variables' => false,
            'add_widgets' => false,
            'add_images' => true,
            'add_directives' => true,
            'use_container' => true,
            'container_class' => 'hor-scroll',
            'height' => '400px',
        ]);
        $data = $config instanceof \Magento\Framework\DataObject
            ? $config->getData()
            : (array) $config;
        return $this->json->serialize($data);
    }

    /**
     * CMS media gallery browser URL for selecting a featured image into #featured_image.
     */
    public function getMediaGalleryUrl(): string
    {
        return $this->getUrl(
            'cms/wysiwyg_images/index',
            [
                'target_element_id' => 'featured_image',
                'type' => 'image',
                'current_tree_path' => base64_encode('wysiwyg'),
            ]
        );
    }

    /**
     * Public storefront URL for the current post (empty if new / missing url_key).
     * Disabled or scheduled posts get a signed ?preview= token.
     */
    public function getStorefrontPreviewUrl(): string
    {
        $post = $this->getPost();
        if (!$post->getId()) {
            return '';
        }
        $urlKey = trim((string) $post->getUrlKey());
        if ($urlKey === '') {
            return '';
        }
        try {
            $storeId = $post->getStoreId() ? (int) $post->getStoreId() : 0;
            if ($storeId > 0) {
                $store = $this->_storeManager->getStore($storeId);
            } else {
                $store = $this->_storeManager->getDefaultStoreView()
                    ?: $this->_storeManager->getStore();
            }
            $url = rtrim($store->getBaseUrl(), '/') . '/blog/' . ltrim($urlKey, '/');
            if ($this->needsPreviewToken($post)) {
                $token = $this->previewToken->create((int) $post->getId());
                if ($token !== '') {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . 'preview=' . rawurlencode($token);
                }
            }
            return $url;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Whether Admin should mint a preview token (disabled draft or future publish).
     */
    public function needsPreviewToken(?Post $post = null): bool
    {
        $post = $post ?: $this->getPost();
        if (!$post || !$post->getId()) {
            return false;
        }
        return !$this->postVisibility->isActiveAndPublished($post);
    }
}
