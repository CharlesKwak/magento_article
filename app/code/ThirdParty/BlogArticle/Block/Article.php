<?php
namespace ThirdParty\BlogArticle\Block;

class Article extends \Magento\Framework\View\Element\Template
{
    public function getHelloMessage()
    {
        return __('Hello Blog Article');
    }
}
