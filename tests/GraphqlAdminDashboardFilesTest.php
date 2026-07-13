<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class GraphqlAdminDashboardFilesTest extends TestCase
{
    public function testFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        foreach ([
            '/Model/Resolver/ApproveBlogComment.php',
            '/Model/Resolver/DeleteBlogComment.php',
            '/Model/Resolver/BlogStats.php',
            '/Block/Adminhtml/Dashboard/Stats.php',
            '/view/adminhtml/layout/adminhtml_dashboard_index.xml',
            '/view/adminhtml/templates/dashboard/stats.phtml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testSchemaAndApiWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $schema = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('view_count: Int', $schema);
        $this->assertStringContainsString('approveBlogComment', $schema);
        $this->assertStringContainsString('deleteBlogComment', $schema);
        $this->assertStringContainsString('blogStats', $schema);
        $this->assertStringContainsString('BlogStats', $schema);

        $iface = file_get_contents($root . '/Api/Data/PostInterface.php');
        $this->assertStringContainsString('VIEW_COUNT', $iface);
        $this->assertStringContainsString('getViewCount', $iface);

        $mapper = file_get_contents($root . '/Model/Resolver/DataMapper/PostMapper.php');
        $this->assertStringContainsString('view_count', $mapper);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.20.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.20.0"', $composer);
    }
}
