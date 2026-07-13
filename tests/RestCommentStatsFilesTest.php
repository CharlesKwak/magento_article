<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RestCommentStatsFilesTest extends TestCase
{
    public function testRestStatsAndCommentListWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Api/BlogStatsManagementInterface.php');
        $this->assertFileExists($root . '/Api/Data/BlogStatsInterface.php');
        $this->assertFileExists($root . '/Model/BlogStatsManagement.php');
        $this->assertFileExists($root . '/Model/Data/BlogStats.php');

        $webapi = file_get_contents($root . '/etc/webapi.xml');
        $this->assertStringContainsString('/V1/blogarticle/stats', $webapi);
        $this->assertStringContainsString('BlogStatsManagementInterface', $webapi);
        $this->assertStringContainsString('method="getList"', $webapi);
        $this->assertStringContainsString('comments/:commentId/approve', $webapi);

        $iface = file_get_contents($root . '/Api/CommentRepositoryInterface.php');
        $this->assertStringContainsString('function getList', $iface);

        $repo = file_get_contents($root . '/Model/CommentRepository.php');
        $this->assertStringContainsString('function getList', $repo);
        $this->assertStringContainsString("'pending'", $repo);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('BlogStatsManagementInterface', $di);
        $this->assertStringContainsString('BlogStatsInterface', $di);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.30.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.30.0"', $composer);
    }
}
