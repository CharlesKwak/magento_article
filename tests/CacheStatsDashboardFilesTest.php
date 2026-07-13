<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CacheStatsDashboardFilesTest extends TestCase
{
    public function testWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Console/Command/StatsCommand.php');
        $this->assertFileExists($root . '/Observer/FlushBlogCache.php');
        $this->assertFileExists($root . '/etc/events.xml');

        $events = file_get_contents($root . '/etc/events.xml');
        $this->assertStringContainsString('model_save_after', $events);
        $this->assertStringContainsString('FlushBlogCache', $events);

        $observer = file_get_contents($root . '/Observer/FlushBlogCache.php');
        $this->assertStringContainsString('instanceof Post', $observer);
        $this->assertStringContainsString('instanceof Comment', $observer);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('StatsCommand', $di);
        $this->assertStringContainsString('blogarticle_stats', $di);

        $statsCmd = file_get_contents($root . '/Console/Command/StatsCommand.php');
        $this->assertStringContainsString('blogarticle:stats', $statsCmd);

        $uploader = file_get_contents($root . '/Model/FeaturedImageUploader.php');
        $this->assertStringContainsString('getImageDimensions', $uploader);
        $this->assertStringContainsString('resolveLocalPath', $uploader);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('getFeaturedImageDimensions', $view);
        $this->assertStringContainsString('getRelatedPosts', $view);

        $dash = file_get_contents($root . '/Block/Adminhtml/Dashboard/Stats.php');
        $this->assertStringContainsString('getRecentPosts', $dash);
        $this->assertStringContainsString('getTopViewedPosts', $dash);

        $tpl = file_get_contents($root . '/view/adminhtml/templates/dashboard/stats.phtml');
        $this->assertStringContainsString('Top viewed', $tpl);
        $this->assertStringContainsString('Recently updated', $tpl);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.28.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.28.0"', $composer);
    }
}
