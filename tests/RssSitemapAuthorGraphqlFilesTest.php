<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RssSitemapAuthorGraphqlFilesTest extends TestCase
{
    public function testFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Model/Sitemap/ItemProvider/Index.php');
        $this->assertFileExists($root . '/Model/Sitemap/ItemProvider/Categories.php');
        $this->assertFileExists($root . '/Model/Sitemap/ItemProvider/Tags.php');
        $this->assertFileExists($root . '/Model/Sitemap/ItemProvider/Posts.php');
        $this->assertFileExists($root . '/Controller/Rss/Feed.php');
    }

    public function testWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $schema = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('author: String', $schema);

        $posts = file_get_contents($root . '/Model/Resolver/Posts.php');
        $this->assertStringContainsString("\$args['author']", $posts);
        $this->assertStringContainsString('PostMapper', $posts);

        $repoIface = file_get_contents($root . '/Api/PostRepositoryInterface.php');
        $this->assertStringContainsString('$author = null', $repoIface);

        $repo = file_get_contents($root . '/Model/PostRepository.php');
        $this->assertStringContainsString('applyAuthorKey', $repo);

        $rss = file_get_contents($root . '/Controller/Rss/Feed.php');
        $this->assertStringContainsString("getParam('category'", $rss);
        $this->assertStringContainsString("getParam('tag'", $rss);
        $this->assertStringContainsString("getParam('author'", $rss);
        $this->assertStringContainsString('applyAuthorKey', $rss);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('blogarticle_categories', $di);
        $this->assertStringContainsString('blogarticle_tags', $di);
        $this->assertStringContainsString('blogarticle_index', $di);
        $this->assertStringContainsString('Sitemap\\ItemProvider\\Categories', $di);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString("params['author']", $article);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.16.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.16.0"', $composer);
    }
}
