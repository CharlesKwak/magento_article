<?php
declare(strict_types=1);

namespace ThirdParty\BlogArticle\Model\Data;

use Magento\Framework\DataObject;
use ThirdParty\BlogArticle\Api\Data\BlogArchiveMonthInterface;

class BlogArchiveMonth extends DataObject implements BlogArchiveMonthInterface
{
    public function getYear()
    {
        return (int) $this->getData('year');
    }

    public function setYear($year)
    {
        return $this->setData('year', (int) $year);
    }

    public function getMonth()
    {
        return (int) $this->getData('month');
    }

    public function setMonth($month)
    {
        return $this->setData('month', (int) $month);
    }

    public function getCount()
    {
        return (int) $this->getData('count');
    }

    public function setCount($count)
    {
        return $this->setData('count', (int) $count);
    }

    public function getLabel()
    {
        return (string) $this->getData('label');
    }

    public function setLabel($label)
    {
        return $this->setData('label', (string) $label);
    }

    public function getUrlPath()
    {
        return (string) $this->getData('url_path');
    }

    public function setUrlPath($urlPath)
    {
        return $this->setData('url_path', (string) $urlPath);
    }

    public function getUrl()
    {
        return (string) $this->getData('url');
    }

    public function setUrl($url)
    {
        return $this->setData('url', (string) $url);
    }
}
