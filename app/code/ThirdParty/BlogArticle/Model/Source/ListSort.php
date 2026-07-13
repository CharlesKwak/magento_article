<?php
namespace ThirdParty\BlogArticle\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Storefront / API post list sort modes.
 */
class ListSort implements OptionSourceInterface
{
    public const NEWEST = 'newest';
    public const OLDEST = 'oldest';
    public const TITLE_ASC = 'title_asc';
    public const TITLE_DESC = 'title_desc';
    public const MOST_VIEWED = 'most_viewed';

    public function toOptionArray()
    {
        return [
            ['value' => self::NEWEST, 'label' => __('Newest first')],
            ['value' => self::OLDEST, 'label' => __('Oldest first')],
            ['value' => self::TITLE_ASC, 'label' => __('Title A–Z')],
            ['value' => self::TITLE_DESC, 'label' => __('Title Z–A')],
            ['value' => self::MOST_VIEWED, 'label' => __('Most viewed')],
        ];
    }

    /**
     * @return string[]
     */
    public static function allowed(): array
    {
        return [
            self::NEWEST,
            self::OLDEST,
            self::TITLE_ASC,
            self::TITLE_DESC,
            self::MOST_VIEWED,
        ];
    }
}
