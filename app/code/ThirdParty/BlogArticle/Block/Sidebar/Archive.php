<?php
namespace ThirdParty\BlogArticle\Block\Sidebar;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use ThirdParty\BlogArticle\Model\Archive as ArchiveModel;
use ThirdParty\BlogArticle\Model\Config;

class Archive extends Template
{
    private $archive;
    private $config;
    private $months;

    public function __construct(
        Context $context,
        ArchiveModel $archive,
        Config $config,
        array $data = []
    ) {
        $this->archive = $archive;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->config->isSidebarEnabled() && $this->config->isArchiveSidebarEnabled();
    }

    public function getBlockTitle(): string
    {
        return (string) __('Archive');
    }

    /**
     * @return array<int, array{year:int,month:int,count:int,label:string,url:string}>
     */
    public function getMonths(): array
    {
        if ($this->months !== null) {
            return $this->months;
        }
        $this->months = [];
        if (!$this->isEnabled()) {
            return $this->months;
        }
        foreach ($this->archive->getMonthlyBuckets($this->config->getArchiveSidebarLimit()) as $bucket) {
            $bucket['url'] = $this->getArchiveUrl((int) $bucket['year'], (int) $bucket['month']);
            $this->months[] = $bucket;
        }
        return $this->months;
    }

    public function getArchiveUrl(int $year, int $month): string
    {
        return $this->getUrl('', [
            '_direct' => sprintf('blog/archive/%04d/%02d', $year, $month),
        ]);
    }

    protected function _toHtml()
    {
        if (!$this->isEnabled() || !$this->getMonths()) {
            return '';
        }
        return parent::_toHtml();
    }
}
