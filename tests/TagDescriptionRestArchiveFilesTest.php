<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class TagDescriptionRestArchiveFilesTest extends TestCase
{
    public function testTagDescriptionAndRestArchives(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $patch = file_get_contents($root . '/Setup/Patch/Schema/AddTagDescription.php');
        $this->assertStringContainsString('description', $patch);
        $this->assertStringContainsString('thirdparty_blogarticle_tag', $patch);

        $schema = file_get_contents($root . '/etc/db_schema.xml');
        $this->assertStringContainsString('thirdparty_blogarticle_tag', $schema);
        $this->assertMatchesRegularExpression(
            '/thirdparty_blogarticle_tag[\s\S]*name="description"/',
            $schema
        );

        $iface = file_get_contents($root . '/Api/Data/TagInterface.php');
        $this->assertStringContainsString('getDescription', $iface);

        $repo = file_get_contents($root . '/Model/TagRepository.php');
        $this->assertStringContainsString('setDescription', $repo);

        $edit = file_get_contents($root . '/view/adminhtml/templates/tag/edit.phtml');
        $this->assertStringContainsString('name="description"', $edit);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('getTagDescription', $article);
        $this->assertStringContainsString('getFilterDescription', $article);

        $tpl = file_get_contents($root . '/view/frontend/templates/article.phtml');
        $this->assertStringContainsString('getFilterDescription', $tpl);

        $gql = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('type BlogTag', $gql);
        $this->assertStringContainsString('description: String', $gql);

        $webapi = file_get_contents($root . '/etc/webapi.xml');
        $this->assertStringContainsString('/V1/blogarticle/archives', $webapi);
        $this->assertStringContainsString('BlogArchiveManagementInterface', $webapi);

        $mgmt = file_get_contents($root . '/Api/BlogArchiveManagementInterface.php');
        $this->assertStringContainsString('getList', $mgmt);

        $impl = file_get_contents($root . '/Model/BlogArchiveManagement.php');
        $this->assertStringContainsString('getMonthlyBuckets', $impl);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('BlogArchiveManagementInterface', $di);
        $this->assertStringContainsString('BlogArchiveMonthInterface', $di);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.29.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.29.0"', $composer);
    }
}
