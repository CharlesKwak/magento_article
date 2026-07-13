<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PaginationFaqCliFilesTest extends TestCase
{
    public function testWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $this->assertFileExists($root . '/Console/Command/PostUpdateCommand.php');

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('getPaginationLinkRels', $article);
        $this->assertStringContainsString('addPaginationLinkRels', $article);
        $this->assertStringContainsString('getFaqItems', $article);
        $this->assertStringContainsString('FAQPage', $article);
        $this->assertStringContainsString("'rel' => 'prev'", $article);
        $this->assertStringContainsString("'rel' => 'next'", $article);

        $listSeo = file_get_contents($root . '/view/frontend/templates/list/seo.phtml');
        $this->assertStringContainsString('getPaginationLinkRels', $listSeo);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isListFaqSchemaEnabled', $config);
        $this->assertStringContainsString('getListFaqSchemaItems', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('list_faq_schema_enabled', $system);
        $this->assertStringContainsString('list_faq_schema_json', $system);

        $create = file_get_contents($root . '/Console/Command/PostCreateCommand.php');
        $this->assertStringContainsString('meta-robots', $create);
        $update = file_get_contents($root . '/Console/Command/PostUpdateCommand.php');
        $this->assertStringContainsString('blogarticle:post:update', $update);
        $this->assertStringContainsString('meta-robots', $update);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('PostUpdateCommand', $di);

        $export = file_get_contents($root . '/Model/PostCsvExporter.php');
        $this->assertStringContainsString("'meta_robots'", $export);

        $show = file_get_contents($root . '/Console/Command/PostShowCommand.php');
        $this->assertStringContainsString('meta_robots', $show);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.22.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.22.0"', $composer);
    }
}
