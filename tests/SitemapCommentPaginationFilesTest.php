<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SitemapCommentPaginationFilesTest extends TestCase
{
    public function testSitemapArchivesAndCommentPagination(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $archives = file_get_contents($root . '/Model/Sitemap/ItemProvider/Archives.php');
        $this->assertStringContainsString('blog/archive/', $archives);
        $this->assertStringContainsString('getMonthlyBuckets', $archives);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('blogarticle_archives', $di);
        $this->assertStringContainsString('ItemProvider\\Archives', $di);

        $iface = file_get_contents($root . '/Api/CommentRepositoryInterface.php');
        $this->assertStringContainsString('getListByPostIdTotalCount', $iface);
        $this->assertStringContainsString('getListTotalCount', $iface);
        $this->assertStringContainsString('$page = 0', $iface);

        $repo = file_get_contents($root . '/Model/CommentRepository.php');
        $this->assertStringContainsString('applyPagination', $repo);
        $this->assertStringContainsString('getListByPostIdTotalCount', $repo);
        $this->assertStringContainsString('getListTotalCount', $repo);

        $webapi = file_get_contents($root . '/etc/webapi.xml');
        $this->assertStringContainsString('/V1/blogarticle/posts/:postId/comments/count', $webapi);
        $this->assertStringContainsString('/V1/blogarticle/comments/count', $webapi);

        $gql = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('BlogCommentList', $gql);
        $this->assertStringContainsString('commentsConnection', $gql);
        $this->assertStringContainsString('pageSize: Int = 0', $gql);

        $conn = file_get_contents($root . '/Model/Resolver/PostCommentsConnection.php');
        $this->assertStringContainsString('getListByPostIdTotalCount', $conn);

        $pc = file_get_contents($root . '/Model/Resolver/PostComments.php');
        $this->assertStringContainsString('pageSize', $pc);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.33.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.33.0"', $composer);
    }
}
