<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ThirdParty\BlogArticle\Api\Data\TagInterface;

interface TagRepositoryInterface
{
    /**
     * @param int $tagId
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\TagInterface
     * @throws NoSuchEntityException
     */
    public function getById($tagId, $activeOnly = true);

    /**
     * @param string $urlKey
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\TagInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey($urlKey, $activeOnly = true);

    /**
     * @param bool $activeOnly
     * @return \ThirdParty\BlogArticle\Api\Data\TagInterface[]
     */
    public function getList($activeOnly = true);

    /**
     * @param \ThirdParty\BlogArticle\Api\Data\TagInterface $tag
     * @return \ThirdParty\BlogArticle\Api\Data\TagInterface
     * @throws LocalizedException
     */
    public function save(TagInterface $tag);

    /**
     * @param int $tagId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($tagId);
}
