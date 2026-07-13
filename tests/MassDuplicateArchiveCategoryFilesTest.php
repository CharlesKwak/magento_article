<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MassDuplicateArchiveCategoryFilesTest extends TestCase
{
    public function testMassDuplicateArchivesCategoryDescription(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $mass = file_get_contents($root . '/Controller/Adminhtml/Post/MassDuplicate.php');
        $this->assertStringContainsString('PostDuplicator', $mass);
        $this->assertStringContainsString('MAX_ITEMS', $mass);

        $grid = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_post_listing.xml');
        $this->assertStringContainsString('massDuplicate', $grid);
        $this->assertStringContainsString('Duplicate', $grid);

        $patch = file_get_contents($root . '/Setup/Patch/Schema/AddCategoryDescription.php');
        $this->assertStringContainsString('description', $patch);

        $schema = file_get_contents($root . '/etc/db_schema.xml');
        $this->assertStringContainsString('name="description"', $schema);

        $iface = file_get_contents($root . '/Api/Data/CategoryInterface.php');
        $this->assertStringContainsString('getDescription', $iface);
        $this->assertStringContainsString('setDescription', $iface);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('getCategoryDescription', $article);

        $tpl = file_get_contents($root . '/view/frontend/templates/article.phtml');
        $this->assertStringContainsString('getFilterDescription', $tpl);
        $this->assertStringContainsString('blog-filter-description', $tpl);

        $catEdit = file_get_contents($root . '/view/adminhtml/templates/category/edit.phtml');
        $this->assertStringContainsString('name="description"', $catEdit);

        $gql = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('blogArchives', $gql);
        $this->assertStringContainsString('BlogArchiveMonth', $gql);
        $this->assertStringContainsString('year: Int', $gql);
        $this->assertStringContainsString('month: Int', $gql);
        $this->assertStringContainsString('description: String', $gql);

        $archives = file_get_contents($root . '/Model/Resolver/Archives.php');
        $this->assertStringContainsString('getMonthlyBuckets', $archives);

        $posts = file_get_contents($root . '/Model/Resolver/Posts.php');
        $this->assertStringContainsString("args['year']", $posts);
        $this->assertStringContainsString("args['month']", $posts);

        $repo = file_get_contents($root . '/Model/PostRepository.php');
        $this->assertStringContainsString('applyYearMonth', $repo);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.29.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.29.0"', $composer);
    }
}
