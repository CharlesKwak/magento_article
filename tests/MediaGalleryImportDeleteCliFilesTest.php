<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MediaGalleryImportDeleteCliFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Block/Adminhtml/Post/Edit.php',
            '/view/adminhtml/templates/post/edit.phtml',
            '/Console/Command/CategoryDeleteCommand.php',
            '/Console/Command/TagDeleteCommand.php',
            '/Console/Command/PostImportCommand.php',
            '/Model/PostCsvImporter.php',
            '/etc/di.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
        $this->assertFileExists(dirname(__DIR__) . '/docs/samples/posts_import_sample.csv');
    }

    public function testWiring(): void
    {
        $root = $this->root();

        $edit = file_get_contents($root . '/Block/Adminhtml/Post/Edit.php');
        $this->assertStringContainsString('getMediaGalleryUrl', $edit);
        $this->assertStringContainsString('cms/wysiwyg_images/index', $edit);

        $phtml = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('MediabrowserUtility', $phtml);
        $this->assertStringContainsString('featured_image_gallery_btn', $phtml);
        $this->assertStringContainsString('mage/adminhtml/browser', $phtml);

        $catDel = file_get_contents($root . '/Console/Command/CategoryDeleteCommand.php');
        $this->assertStringContainsString('blogarticle:category:delete', $catDel);
        $this->assertStringContainsString('--force', $catDel);

        $tagDel = file_get_contents($root . '/Console/Command/TagDeleteCommand.php');
        $this->assertStringContainsString('blogarticle:tag:delete', $tagDel);

        $importCmd = file_get_contents($root . '/Console/Command/PostImportCommand.php');
        $this->assertStringContainsString('blogarticle:post:import', $importCmd);
        $this->assertStringContainsString('dry-run', $importCmd);

        $importer = file_get_contents($root . '/Model/PostCsvImporter.php');
        $this->assertStringContainsString('title', $importer);
        $this->assertStringContainsString('content', $importer);
        $this->assertStringContainsString('--update', $importer) || $this->assertStringContainsString('update', $importer);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('CategoryDeleteCommand', $di);
        $this->assertStringContainsString('TagDeleteCommand', $di);
        $this->assertStringContainsString('PostImportCommand', $di);

        $sample = file_get_contents(dirname(__DIR__) . '/docs/samples/posts_import_sample.csv');
        $this->assertStringContainsString('title,content', $sample);
    }

    public function testModuleVersionIs290(): void
    {
        $module = file_get_contents($this->root() . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.31.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.31.0"', $composer);
    }
}
