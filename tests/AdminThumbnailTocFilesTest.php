<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class AdminThumbnailTocFilesTest extends TestCase
{
    public function testThumbnailColumnAndTocWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $column = file_get_contents($root . '/Ui/Component/Listing/Column/FeaturedImage.php');
        $this->assertStringContainsString('resolveUrl', $column);
        $this->assertStringContainsString('<img', $column);
        $this->assertStringContainsString('FeaturedImageUploader', $column);

        $grid = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_post_listing.xml');
        $this->assertStringContainsString('FeaturedImage', $grid);
        $this->assertStringContainsString('featured_image', $grid);
        $this->assertStringContainsString('ui/grid/cells/html', $grid);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isTocEnabled', $config);
        $this->assertStringContainsString('getTocMinHeadings', $config);
        $this->assertStringContainsString('XML_PATH_TOC_ENABLED', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('toc_enabled', $system);
        $this->assertStringContainsString('toc_min_headings', $system);
        $this->assertStringContainsString('Show Table of Contents', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<toc_enabled>1</toc_enabled>', $defaults);
        $this->assertStringContainsString('<toc_min_headings>2</toc_min_headings>', $defaults);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('getTocItems', $view);
        $this->assertStringContainsString('injectHeadingIdsAndBuildToc', $view);
        $this->assertStringContainsString('slugifyHeading', $view);

        $tpl = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('getTocItems', $tpl);
        $this->assertStringContainsString('blog-toc', $tpl);
        $this->assertStringContainsString('On this page', $tpl);

        $layout = file_get_contents($root . '/view/frontend/layout/blog_post_view.xml');
        $this->assertStringContainsString('blogarticle-toc.css', $layout);

        $css = file_get_contents($root . '/view/frontend/web/css/blogarticle-toc.css');
        $this->assertStringContainsString('.blog-toc', $css);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.25.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.25.0"', $composer);
    }
}
