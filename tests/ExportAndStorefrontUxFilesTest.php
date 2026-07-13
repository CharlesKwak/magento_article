<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ExportAndStorefrontUxFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testExportAndUxFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Model/PostCsvExporter.php',
            '/Model/CommentCsvExporter.php',
            '/Model/TaxonomyCsvExporter.php',
            '/Console/Command/PostExportCommand.php',
            '/Console/Command/CommentExportCommand.php',
            '/Console/Command/CategoryExportCommand.php',
            '/Console/Command/TagExportCommand.php',
            '/Block/Post/View.php',
            '/Block/Article.php',
            '/view/frontend/templates/post/view.phtml',
            '/etc/di.xml',
            '/etc/module.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testExportAndUxWiring(): void
    {
        $root = $this->root();

        $postExport = file_get_contents($root . '/Console/Command/PostExportCommand.php');
        $this->assertStringContainsString('blogarticle:post:export', $postExport);

        $commentExport = file_get_contents($root . '/Console/Command/CommentExportCommand.php');
        $this->assertStringContainsString('blogarticle:comment:export', $commentExport);

        $catExport = file_get_contents($root . '/Console/Command/CategoryExportCommand.php');
        $this->assertStringContainsString('blogarticle:category:export', $catExport);

        $tagExport = file_get_contents($root . '/Console/Command/TagExportCommand.php');
        $this->assertStringContainsString('blogarticle:tag:export', $tagExport);

        $exporter = file_get_contents($root . '/Model/PostCsvExporter.php');
        $this->assertStringContainsString('HEADERS', $exporter);
        $this->assertStringContainsString('title', $exporter);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('addBreadcrumbs', $view);
        $this->assertStringContainsString('getShareLinks', $view);
        $this->assertStringContainsString('getReadingMinutes', $view);
        $this->assertStringContainsString('getNeighborPosts', $view);
        $this->assertStringContainsString('getTags', $view);
        $this->assertStringContainsString('getCategoryUrl', $view);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('breadcrumbs', $article);

        $phtml = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('blog-share', $phtml);
        $this->assertStringContainsString('blog-post-nav', $phtml);
        $this->assertStringContainsString('blog-reading-time', $phtml);
        $this->assertStringContainsString('blog-copy-link', $phtml);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('PostExportCommand', $di);
        $this->assertStringContainsString('CommentExportCommand', $di);
        $this->assertStringContainsString('CategoryExportCommand', $di);
        $this->assertStringContainsString('TagExportCommand', $di);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.13.0"', $module);
    }

    public function testModuleVersionIs290(): void
    {
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.13.0"', $composer);
    }
}
