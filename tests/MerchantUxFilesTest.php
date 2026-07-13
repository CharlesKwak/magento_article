<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Smoke coverage for merchant UX features (v2.10): widget, sidebar, topmenu, CMS slots.
 */
class MerchantUxFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testMerchantUxFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Plugin/Topmenu.php',
            '/Block/Link.php',
            '/Block/Widget/Posts.php',
            '/Block/Sidebar/Recent.php',
            '/Block/Sidebar/Search.php',
            '/etc/widget.xml',
            '/view/frontend/layout/default.xml',
            '/view/frontend/layout/blog_index_index.xml',
            '/view/frontend/layout/blog_post_view.xml',
            '/view/frontend/templates/widget/posts.phtml',
            '/view/frontend/templates/sidebar/recent.phtml',
            '/view/frontend/templates/sidebar/search.phtml',
            '/i18n/en_US.csv',
            '/i18n/ko_KR.csv',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testConfigAndWiring(): void
    {
        $root = $this->root();
        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('getBlogName', $config);
        $this->assertStringContainsString('isShowTopMenu', $config);
        $this->assertStringContainsString('isSidebarEnabled', $config);
        $this->assertStringContainsString('getListMetaTitle', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('id="general"', $system);
        $this->assertStringContainsString('id="sidebar"', $system);
        $this->assertStringContainsString('id="seo"', $system);
        $this->assertStringContainsString('blog_name', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<show_top_menu>1</show_top_menu>', $defaults);
        $this->assertStringContainsString('<sidebar>', $defaults);

        $di = file_get_contents($root . '/etc/frontend/di.xml');
        $this->assertStringContainsString('ThirdParty\\BlogArticle\\Plugin\\Topmenu', $di);
        $this->assertStringContainsString('Magento\\Theme\\Block\\Html\\Topmenu', $di);

        $widget = file_get_contents($root . '/etc/widget.xml');
        $this->assertStringContainsString('thirdparty_blogarticle_posts', $widget);
        $this->assertStringContainsString('ThirdParty\\BlogArticle\\Block\\Widget\\Posts', $widget);

        $postLayout = file_get_contents($root . '/view/frontend/layout/blog_post_view.xml');
        $this->assertStringContainsString('blogarticle_view_under_content', $postLayout);
        $this->assertStringContainsString('blogarticle_view_above_comment', $postLayout);
        $this->assertStringContainsString('2columns-right', $postLayout);

        $listLayout = file_get_contents($root . '/view/frontend/layout/blog_index_index.xml');
        $this->assertStringContainsString('blog.sidebar.recent', $listLayout);
        $this->assertStringContainsString('2columns-right', $listLayout);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('Magento_Widget', $module);
        $this->assertStringContainsString('Magento_Theme', $module);
        $this->assertStringContainsString('setup_version="2.26.0"', $module);
    }

    public function testPackageVersionIs2100(): void
    {
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.26.0"', $composer);
    }
}
