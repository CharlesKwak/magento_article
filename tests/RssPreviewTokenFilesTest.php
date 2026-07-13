<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RssPreviewTokenFilesTest extends TestCase
{
    public function testRssArchiveFiltersAndPreviewToken(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $rss = file_get_contents($root . '/Controller/Rss/Feed.php');
        $this->assertStringContainsString('applyYearMonth', $rss);
        $this->assertStringContainsString("getParam('year'", $rss);
        $this->assertStringContainsString("getParam('month'", $rss);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString("params['year']", $article);
        $this->assertStringContainsString('getYearFilter', $article);

        $token = file_get_contents($root . '/Model/PreviewToken.php');
        $this->assertStringContainsString('hash_hmac', $token);
        $this->assertStringContainsString('isValid', $token);
        $this->assertStringContainsString('create', $token);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('getPreviewTokenTtlSeconds', $config);
        $this->assertStringContainsString('preview_token_ttl_hours', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('preview_token_ttl_hours', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<preview_token_ttl_hours>48</preview_token_ttl_hours>', $defaults);

        $router = file_get_contents($root . '/Controller/Router.php');
        $this->assertStringContainsString('PreviewToken', $router);
        $this->assertStringContainsString('allowPreview', $router);

        $ctrl = file_get_contents($root . '/Controller/Post/View.php');
        $this->assertStringContainsString('allowPreview', $ctrl);
        $this->assertStringContainsString('previewToken', $ctrl);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('isPreviewMode', $view);
        $this->assertStringContainsString('NOINDEX,NOFOLLOW', $view);

        $tpl = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('isPreviewMode', $tpl);
        $this->assertStringContainsString('blog-preview-banner', $tpl);

        $edit = file_get_contents($root . '/Block/Adminhtml/Post/Edit.php');
        $this->assertStringContainsString('needsPreviewToken', $edit);
        $this->assertStringContainsString('previewToken', $edit);

        $editTpl = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('Preview draft', $editTpl);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.31.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.31.0"', $composer);
    }
}
