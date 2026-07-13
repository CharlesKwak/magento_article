<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CategorySortShareOgFilesTest extends TestCase
{
    public function testCategorySortShareAndOgAuthor(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $patch = file_get_contents($root . '/Setup/Patch/Schema/AddCategorySortOrder.php');
        $this->assertStringContainsString('sort_order', $patch);

        $schema = file_get_contents($root . '/etc/db_schema.xml');
        $this->assertStringContainsString('name="sort_order"', $schema);

        $iface = file_get_contents($root . '/Api/Data/CategoryInterface.php');
        $this->assertStringContainsString('getSortOrder', $iface);
        $this->assertStringContainsString('setSortOrder', $iface);

        $repo = file_get_contents($root . '/Model/CategoryRepository.php');
        $this->assertStringContainsString("setOrder('sort_order'", $repo);
        $this->assertStringContainsString('setSortOrder', $repo);

        $edit = file_get_contents($root . '/view/adminhtml/templates/category/edit.phtml');
        $this->assertStringContainsString('name="sort_order"', $edit);

        $grid = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_category_listing.xml');
        $this->assertStringContainsString('sort_order', $grid);
        $this->assertStringContainsString('Sort Order', $grid);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString("setOrder('sort_order'", $article);

        $gql = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('sort_order: Int', $gql);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isShareLinksEnabled', $config);
        $this->assertStringContainsString('XML_PATH_SHARE_LINKS', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('show_share_links', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<show_share_links>1</show_share_links>', $defaults);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('isShareLinksEnabled', $view);
        $this->assertStringContainsString('article:published_time', $view);
        $this->assertStringContainsString('article:modified_time', $view);
        $this->assertStringContainsString('article:section', $view);
        $this->assertStringContainsString('article:tag', $view);
        $this->assertStringContainsString('getAuthorUrl()', $view);
        $this->assertStringContainsString('formatOpenGraphDate', $view);

        $tpl = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('if ($share)', $tpl);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.33.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.33.0"', $composer);
    }
}
