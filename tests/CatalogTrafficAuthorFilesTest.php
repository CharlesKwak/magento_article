<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CatalogTrafficAuthorFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testP2FilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Setup/Patch/Schema/AddViewCountAndProductLink.php',
            '/Model/PostViewCounter.php',
            '/Model/PostProductLink.php',
            '/Block/Sidebar/MostViewed.php',
            '/Block/Product/RelatedPosts.php',
            '/view/frontend/templates/sidebar/mostviewed.phtml',
            '/view/frontend/templates/product/related_posts.phtml',
            '/view/frontend/layout/catalog_product_view.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
        $this->assertFileExists(dirname(__DIR__) . '/docs/MIGRATION_GUIDE.md');
    }

    public function testWiring(): void
    {
        $root = $this->root();
        $patch = file_get_contents($root . '/Setup/Patch/Schema/AddViewCountAndProductLink.php');
        $this->assertStringContainsString('view_count', $patch);
        $this->assertStringContainsString('thirdparty_blogarticle_post_product', $patch);

        $controller = file_get_contents($root . '/Controller/Post/View.php');
        $this->assertStringContainsString('PostViewCounter', $controller);
        $this->assertStringContainsString('increment', $controller);

        $router = file_get_contents($root . '/Controller/Router.php');
        $this->assertStringContainsString('blog/author/', $router);
        $this->assertStringContainsString("'author'", $router);

        $filter = file_get_contents($root . '/Model/PostFilter.php');
        $this->assertStringContainsString('applyAuthorKey', $filter);

        $save = file_get_contents($root . '/Controller/Adminhtml/Post/Save.php');
        $this->assertStringContainsString('product_skus', $save);
        $this->assertStringContainsString('PostProductLink', $save);

        $edit = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('product_skus', $edit);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('Magento_Catalog', $module);
        $this->assertStringContainsString('setup_version="2.26.0"', $module);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isMostViewedEnabled', $config);
        $this->assertStringContainsString('isProductRelatedPostsEnabled', $config);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.26.0"', $composer);
    }
}
