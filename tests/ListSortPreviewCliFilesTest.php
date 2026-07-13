<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ListSortPreviewCliFilesTest extends TestCase
{
    public function testListSortAndPreviewCli(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $sort = file_get_contents($root . '/Model/Source/ListSort.php');
        $this->assertStringContainsString('most_viewed', $sort);
        $this->assertStringContainsString('title_asc', $sort);

        $filter = file_get_contents($root . '/Model/PostFilter.php');
        $this->assertStringContainsString('function applySort', $filter);
        $this->assertStringContainsString('MOST_VIEWED', $filter);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('getDefaultListSort', $config);
        $this->assertStringContainsString('XML_PATH_LIST_SORT', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('default_sort', $system);
        $this->assertStringContainsString('ListSort', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<default_sort>newest</default_sort>', $defaults);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('getListSort', $article);
        $this->assertStringContainsString('applySort', $article);

        $tpl = file_get_contents($root . '/view/frontend/templates/article.phtml');
        $this->assertStringContainsString('name="sort"', $tpl);
        $this->assertStringContainsString('most_viewed', $tpl);

        $repo = file_get_contents($root . '/Model/PostRepository.php');
        $this->assertStringContainsString('$sort = null', $repo);
        $this->assertStringContainsString('getDefaultListSort', $repo);

        $gql = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('sort: String', $gql);

        $posts = file_get_contents($root . '/Model/Resolver/Posts.php');
        $this->assertStringContainsString("args['sort']", $posts);

        $cli = file_get_contents($root . '/Console/Command/PostPreviewUrlCommand.php');
        $this->assertStringContainsString('blogarticle:post:preview-url', $cli);
        $this->assertStringContainsString('PreviewToken', $cli);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('PostPreviewUrlCommand', $di);

        $rss = file_get_contents($root . '/Controller/Rss/Feed.php');
        $this->assertStringContainsString('applySort', $rss);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.33.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.33.0"', $composer);
    }
}
