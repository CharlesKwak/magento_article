<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsApproved implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')],
        ];
    }
}
