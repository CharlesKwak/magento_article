<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ArchiveRelatedFilesTest extends TestCase
{
    public function testArchiveAndRelatedWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $filter = file_get_contents($root . '/Model/PostFilter.php');
        $this->assertStringContainsString('applyYearMonth', $filter);

        $archive = file_get_contents($root . '/Model/Archive.php');
        $this->assertStringContainsString('getMonthlyBuckets', $archive);
        $this->assertStringContainsString('YEAR(', $archive);

        $sidebar = file_get_contents($root . '/Block/Sidebar/Archive.php');
        $this->assertStringContainsString('isArchiveSidebarEnabled', $sidebar);
        $this->assertStringContainsString('blog/archive/', $sidebar);

        $tpl = file_get_contents($root . '/view/frontend/templates/sidebar/archive.phtml');
        $this->assertStringContainsString('blogarticle-archive', $tpl);

        $router = file_get_contents($root . '/Controller/Router.php');
        $this->assertStringContainsString("'archive'", $router);
        $this->assertStringContainsString('blog/archive/', $router);
        $this->assertStringContainsString("setParam('year'", $router);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('getYearFilter', $article);
        $this->assertStringContainsString('getMonthFilter', $article);
        $this->assertStringContainsString('applyYearMonth', $article);
        $this->assertStringContainsString('Archive:', $article);

        $repo = file_get_contents($root . '/Model/PostRepository.php');
        $this->assertStringContainsString('collectRelatedCandidates', $repo);
        $this->assertStringContainsString('getTagIdsForPost', $repo);
        $this->assertStringContainsString('blog_rel_pt', $repo);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isArchiveSidebarEnabled', $config);
        $this->assertStringContainsString('getRelatedPostsLimit', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('show_archive', $system);
        $this->assertStringContainsString('related_posts_limit', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<show_archive>1</show_archive>', $defaults);
        $this->assertStringContainsString('<related_posts_limit>3</related_posts_limit>', $defaults);

        $listLayout = file_get_contents($root . '/view/frontend/layout/blog_index_index.xml');
        $this->assertStringContainsString('blog.sidebar.archive', $listLayout);

        $viewLayout = file_get_contents($root . '/view/frontend/layout/blog_post_view.xml');
        $this->assertStringContainsString('blog.sidebar.archive', $viewLayout);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('getRelatedPostsLimit', $view);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.31.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.31.0"', $composer);
    }
}
