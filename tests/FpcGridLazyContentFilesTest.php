<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FpcGridLazyContentFilesTest extends TestCase
{
    public function testWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $observer = file_get_contents($root . '/Observer/FlushBlogCache.php');
        $this->assertStringContainsString('PageCache\\Model\\Cache\\Type', $observer);
        $this->assertStringContainsString('pageCache', $observer);
        $this->assertStringContainsString('CLEANING_MODE_MATCHING_ANY_TAG', $observer);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('Magento_PageCache', $module);
        $this->assertStringContainsString('setup_version="2.29.0"', $module);

        $grid = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_post_listing.xml');
        $this->assertStringContainsString('view_count', $grid);
        $this->assertStringContainsString('Views', $grid);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('getPreparedContentHtml', $view);
        $this->assertStringContainsString('loading="lazy"', $view);
        $this->assertStringContainsString("'img'", $view);

        $tpl = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('getPreparedContentHtml', $tpl);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.29.0"', $composer);
    }
}
