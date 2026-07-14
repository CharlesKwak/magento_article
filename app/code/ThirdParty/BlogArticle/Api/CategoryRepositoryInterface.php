<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\Data\CategoryInterface;

interface CategoryRepositoryInterface
{
    /**
     * @param int $categoryId
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getById($categoryId, $activeOnly = true);

    /**
     * @param string $urlKey
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey($urlKey, $activeOnly = true);

    /**
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\CategoryInterface[]
     */
    public function getList($activeOnly = true);

    /**
     * @param \ThirdParty\BlogArticle\Api\Data\CategoryInterface $category
     * @return \ThirdParty\BlogArticle\Api\Data\CategoryInterface
     * @throws LocalizedException
     */
    public function save(CategoryInterface $category);

    /**
     * @param int $categoryId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($categoryId);
}
