<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PackagingAndSchemaFilesTest extends TestCase
{
    public function testDeclarativeSchemaAndWordpressImportFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/etc/db_schema.xml');
        $this->assertFileExists($root . '/etc/db_schema_whitelist.json');
        $this->assertFileExists($root . '/Model/Import/WordPressCsvMapper.php');
        $this->assertFileExists(dirname(__DIR__) . '/docs/samples/wordpress_posts_import_sample.csv');
        $this->assertFileExists(dirname(__DIR__) . '/docs/PACKAGIST.md');
    }

    public function testSchemaAndImporterWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $schema = file_get_contents($root . '/etc/db_schema.xml');
        $this->assertStringContainsString('thirdparty_blogarticle_post', $schema);
        $this->assertStringContainsString('view_count', $schema);
        $this->assertStringContainsString('thirdparty_blogarticle_post_product', $schema);
        $this->assertStringContainsString('parent_id', $schema);

        $importer = file_get_contents($root . '/Model/PostCsvImporter.php');
        $this->assertStringContainsString('FORMAT_WORDPRESS', $importer);
        $this->assertStringContainsString('WordPressCsvMapper', $importer);

        $cmd = file_get_contents($root . '/Console/Command/PostImportCommand.php');
        $this->assertStringContainsString('format', $cmd);
        $this->assertStringContainsString('wordpress', $cmd);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.24.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.24.0"', $composer);
    }
}
