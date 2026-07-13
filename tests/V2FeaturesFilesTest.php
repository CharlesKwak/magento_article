<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class V2FeaturesFilesTest extends TestCase
{
    public function testV2FilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/view/adminhtml/ui_component/blogarticle_category_listing.xml');
        $this->assertFileExists($root . '/view/adminhtml/ui_component/blogarticle_tag_listing.xml');
        $this->assertFileExists($root . '/Controller/Adminhtml/Category/MassDelete.php');
        $this->assertFileExists($root . '/Controller/Adminhtml/Tag/MassDelete.php');
        $this->assertFileExists($root . '/Cron/FlushScheduledPostCache.php');
        $this->assertFileExists($root . '/etc/crontab.xml');
        $filter = file_get_contents($root . '/Model/PostFilter.php');
        $this->assertStringContainsString('applyPublishedOnly', $filter);
    }
    public function testModuleVersionIs200(): void
    {
        $c = file_get_contents(dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.15.0"', $c);
    }
}
