<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ReadingProgressPublishedFilesTest extends TestCase
{
    public function testReadingProgressAndPublishedColumn(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isReadingProgressEnabled', $config);
        $this->assertStringContainsString('XML_PATH_READING_PROGRESS', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('reading_progress', $system);
        $this->assertStringContainsString('Show Reading Progress Bar', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<reading_progress>1</reading_progress>', $defaults);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('isReadingProgressEnabled', $view);

        $tpl = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('blog-reading-progress', $tpl);
        $this->assertStringContainsString('blog-reading-progress-bar', $tpl);

        $css = file_get_contents($root . '/view/frontend/web/css/blogarticle-progress.css');
        $this->assertStringContainsString('.blog-reading-progress', $css);

        $layout = file_get_contents($root . '/view/frontend/layout/blog_post_view.xml');
        $this->assertStringContainsString('blogarticle-progress.css', $layout);

        $grid = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_post_listing.xml');
        $this->assertStringContainsString('published_at', $grid);
        $this->assertStringContainsString('Published', $grid);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.33.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.33.0"', $composer);
    }
}
