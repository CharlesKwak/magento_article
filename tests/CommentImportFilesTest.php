<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CommentImportFilesTest extends TestCase
{
    public function testCommentImportFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Model/CommentCsvImporter.php');
        $this->assertFileExists($root . '/Console/Command/CommentImportCommand.php');
        $this->assertFileExists(dirname(__DIR__) . '/docs/samples/comments_import_sample.csv');
    }

    public function testWiringAndVersion(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('CommentImportCommand', $di);
        $this->assertStringContainsString('blogarticle_comment_import', $di);

        $importer = file_get_contents($root . '/Model/CommentCsvImporter.php');
        $this->assertStringContainsString('post_url_key', $importer);
        $this->assertStringContainsString('author_name', $importer);

        $postImporter = file_get_contents($root . '/Model/PostCsvImporter.php');
        $this->assertStringContainsString('product_skus', $postImporter);
        $this->assertStringContainsString('PostProductLink', $postImporter);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.20.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.20.0"', $composer);
    }
}
