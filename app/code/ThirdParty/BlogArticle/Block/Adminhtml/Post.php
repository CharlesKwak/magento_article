<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use ThirdParty\BlogArticle\Model\CategoryFactory;
use ThirdParty\BlogArticle\Model\PostFilter;
use ThirdParty\BlogArticle\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;

class Post extends Template
{
    protected CollectionFactory $collectionFactory;
    protected $formKey;
    private UrlInterface $frontendUrlBuilder;
    private PostFilter $postFilter;
    private CategoryCollectionFactory $categoryCollectionFactory;
    private CategoryFactory $categoryFactory;
    private array $categoryNameCache = [];

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        UrlInterface $frontendUrlBuilder,
        PostFilter $postFilter,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->postFilter = $postFilter;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Post\Collection
     */
    public function getPosts()
    {
        $collection = $this->collectionFactory->create();
        $this->postFilter->applySearch($collection, $this->getSearchQuery());

        $status = $this->getStatusFilter();
        if ($status === '1' || $status === '0') {
            $collection->addFieldToFilter('is_active', (int) $status);
        }

        $categoryId = $this->getCategoryIdFilter();
        if ($categoryId) {
            $this->postFilter->applyCategoryId($collection, $categoryId);
        }

        $collection->setOrder('post_id', 'DESC');
        return $collection;
    }

    public function getSearchQuery(): string
    {
        return trim((string) $this->getRequest()->getParam('q', ''));
    }

    public function getStatusFilter(): string
    {
        $status = $this->getRequest()->getParam('status', '');
        return in_array($status, ['0', '1'], true) ? $status : '';
    }

    public function getCategoryIdFilter(): ?int
    {
        $categoryId = (int) $this->getRequest()->getParam('category_id', 0);
        return $categoryId > 0 ? $categoryId : null;
    }

    /**
     * @return \ThirdParty\BlogArticle\Model\ResourceModel\Category\Collection
     */
    public function getCategoryOptions()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->setOrder('sort_order', 'ASC');
        $collection->setOrder('name', 'ASC');
        return $collection;
    }

    /**
     * @param \ThirdParty\BlogArticle\Model\Post $post
     * @return string
     */
    public function getCategoryName($post): string
    {
        $categoryId = (int) $post->getCategoryId();
        if ($categoryId <= 0) {
            return '';
        }
        if (!array_key_exists($categoryId, $this->categoryNameCache)) {
            $category = $this->categoryFactory->create()->load($categoryId);
            $this->categoryNameCache[$categoryId] = $category->getId()
                ? (string) $category->getName()
                : '';
        }
        return $this->categoryNameCache[$categoryId];
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getNewUrl()
    {
        return $this->getUrl('blogarticle/post/new');
    }

    public function getFilterFormAction()
    {
        return $this->getUrl('blogarticle/post/index');
    }

    public function getEditUrl($postId)
    {
        return $this->getUrl('blogarticle/post/edit', ['post_id' => (int) $postId]);
    }

    public function getDeleteUrl($postId)
    {
        return $this->getUrl(
            'blogarticle/post/delete',
            [
                'post_id' => (int) $postId,
                'form_key' => $this->getFormKey(),
            ]
        );
    }

    public function getFrontendViewUrl($post)
    {
        $urlKey = (string) $post->getUrlKey();
        if ($urlKey !== '') {
            return $this->frontendUrlBuilder->getUrl('', ['_direct' => 'blog/' . $urlKey, '_nosid' => true]);
        }

        return $this->frontendUrlBuilder->getUrl(
            'blog/post/view',
            ['id' => (int) $post->getId(), '_nosid' => true]
        );
    }
}
