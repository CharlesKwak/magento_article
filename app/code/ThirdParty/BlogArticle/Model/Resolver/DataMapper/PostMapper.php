<?php
namespace ThirdParty\BlogArticle\Model\Resolver\DataMapper;

use ThirdParty\BlogArticle\Api\Data\PostInterface;

class PostMapper
{
    /**
     * @param array $input
     * @param PostInterface $post
     * @param bool $partial when true, only set provided keys
     * @return void
     */
    public function mapInputToPost(array $input, PostInterface $post, bool $partial = false): void
    {
        $map = [
            'title' => 'setTitle',
            'author' => 'setAuthor',
            'url_key' => 'setUrlKey',
            'content' => 'setContent',
            'excerpt' => 'setExcerpt',
            'featured_image' => 'setFeaturedImage',
            'meta_title' => 'setMetaTitle',
            'meta_description' => 'setMetaDescription',
            'is_active' => 'setIsActive',
            'category_id' => 'setCategoryId',
            'store_id' => 'setStoreId',
            'published_at' => 'setPublishedAt',
        ];
        foreach ($map as $key => $setter) {
            if (!array_key_exists($key, $input)) {
                if ($partial) {
                    continue;
                }
                // required fields checked by repository
                continue;
            }
            $value = $input[$key];
            if (in_array($key, ['is_active', 'category_id', 'store_id'], true) && $value !== null) {
                $value = (int) $value;
            }
            $post->{$setter}($value);
        }
        if (array_key_exists('tag_ids', $input) && is_array($input['tag_ids'])) {
            $post->setTagIds(array_map('intval', $input['tag_ids']));
        }
    }

    /**
     * @param PostInterface $item
     * @return array
     */
    public function toGraphQlArray(PostInterface $item): array
    {
        return [
            'post_id' => $item->getPostId(),
            'title' => $item->getTitle(),
            'author' => $item->getAuthor(),
            'url_key' => $item->getUrlKey(),
            'content' => $item->getContent(),
            'excerpt' => $item->getExcerpt(),
            'featured_image' => $item->getFeaturedImage(),
            'meta_title' => $item->getMetaTitle(),
            'meta_description' => $item->getMetaDescription(),
            'is_active' => $item->getIsActive(),
            'view_count' => method_exists($item, 'getViewCount') ? (int) $item->getViewCount() : 0,
            'category_id' => $item->getCategoryId(),
            'store_id' => $item->getStoreId(),
            'tag_ids' => $item->getTagIds() ?: [],
            'creation_time' => $item->getCreationTime(),
            'update_time' => $item->getUpdateTime(),
            'published_at' => $item->getPublishedAt(),
            'model' => $item,
        ];
    }
}
