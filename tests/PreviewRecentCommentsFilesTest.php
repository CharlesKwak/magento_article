<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PreviewRecentCommentsFilesTest extends TestCase
{
    public function testPreviewAndRecentCommentsWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $edit = file_get_contents($root . '/Block/Adminhtml/Post/Edit.php');
        $this->assertStringContainsString('getStorefrontPreviewUrl', $edit);
        $this->assertStringContainsString('/blog/', $edit);

        $tpl = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('getStorefrontPreviewUrl', $tpl);
        $this->assertStringContainsString('View on storefront', $tpl);

        $actions = file_get_contents($root . '/Ui/Component/Listing/Column/PostActions.php');
        $this->assertStringContainsString('buildStorefrontUrl', $actions);
        $this->assertStringContainsString("'view'", $actions);
        $this->assertStringContainsString('StoreManagerInterface', $actions);

        $sidebar = file_get_contents($root . '/Block/Sidebar/RecentComments.php');
        $this->assertStringContainsString('isRecentCommentsSidebarEnabled', $sidebar);
        $this->assertStringContainsString('getRecentCommentsSidebarCount', $sidebar);

        $sidebarTpl = file_get_contents($root . '/view/frontend/templates/sidebar/recent_comments.phtml');
        $this->assertStringContainsString('blogarticle-recent-comments', $sidebarTpl);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isRecentCommentsSidebarEnabled', $config);
        $this->assertStringContainsString('XML_PATH_SIDEBAR_RECENT_COMMENTS', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('show_recent_comments', $system);
        $this->assertStringContainsString('recent_comments_count', $system);

        $defaults = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('<show_recent_comments>1</show_recent_comments>', $defaults);

        $list = file_get_contents($root . '/view/frontend/layout/blog_index_index.xml');
        $this->assertStringContainsString('blog.sidebar.recent.comments', $list);

        $view = file_get_contents($root . '/view/frontend/layout/blog_post_view.xml');
        $this->assertStringContainsString('blog.sidebar.recent.comments', $view);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.31.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.31.0"', $composer);
    }
}
