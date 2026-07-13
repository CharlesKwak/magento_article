<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CanonicalHreflangReadingFilesTest extends TestCase
{
    public function testFilesAndWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Model/Seo/HreflangBuilder.php');
        $this->assertFileExists($root . '/view/frontend/layout/blog_post_view_reading.xml');

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isHreflangEnabled', $config);
        $this->assertStringContainsString('isReadingModeLinkEnabled', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('hreflang_enabled', $system);
        $this->assertStringContainsString('reading_mode_link', $system);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('addRemotePageAsset', $article);
        $this->assertStringContainsString('getHreflangLinks', $article);
        $this->assertStringContainsString('HreflangBuilder', $article);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('addRemotePageAsset', $view);
        $this->assertStringContainsString('getReadingModeUrl', $view);
        $this->assertStringContainsString('isReadingMode', $view);

        $controller = file_get_contents($root . '/Controller/Post/View.php');
        $this->assertStringContainsString("getParam('reading')", $controller);
        $this->assertStringContainsString('blog_post_view_reading', $controller);
        $this->assertStringContainsString('1column', $controller);

        $listSeo = file_get_contents($root . '/view/frontend/templates/list/seo.phtml');
        $this->assertStringContainsString('getHreflangLinks', $listSeo);
        $this->assertStringContainsString('rel="alternate"', $listSeo);

        $postSeo = file_get_contents($root . '/view/frontend/templates/post/seo.phtml');
        $this->assertStringContainsString('getHreflangLinks', $postSeo);

        $postView = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('Reading mode', $postView);
        $this->assertStringContainsString('Exit reading mode', $postView);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.28.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.28.0"', $composer);
    }
}
