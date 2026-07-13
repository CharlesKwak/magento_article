<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class RssAndUploadFilesTest extends TestCase
{
    public function testRssAndUploadFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Controller/Rss/Feed.php');
        $this->assertFileExists($root . '/Model/FeaturedImageUploader.php');
        $this->assertFileExists($root . '/Setup/Patch/Schema/AddExcerptAndPublishedAt.php');
        $install = file_get_contents($root . '/Setup/InstallSchema.php');
        $this->assertStringContainsString('excerpt', $install);
        $this->assertStringContainsString('published_at', $install);
    }
    public function testModuleVersionIs180(): void
    {
        $c = file_get_contents(dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.32.0"', $c);
    }
}
