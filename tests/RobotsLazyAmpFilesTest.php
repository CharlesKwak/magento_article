<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RobotsLazyAmpFilesTest extends TestCase
{
    public function testFilesAndWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Setup/Patch/Schema/AddMetaRobotsColumn.php');

        $schema = file_get_contents($root . '/etc/db_schema.xml');
        $this->assertStringContainsString('meta_robots', $schema);

        $iface = file_get_contents($root . '/Api/Data/PostInterface.php');
        $this->assertStringContainsString('META_ROBOTS', $iface);
        $this->assertStringContainsString('getMetaRobots', $iface);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('getRobotsContent', $view);
        $this->assertStringContainsString('setRobots', $view);
        $this->assertStringContainsString('getAmpHtmlUrl', $view);
        $this->assertStringContainsString('isLazyLoadImagesEnabled', $view);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isLazyLoadImagesEnabled', $config);
        $this->assertStringContainsString('isAmpHtmlEnabled', $config);
        $this->assertStringContainsString('getAmpHtmlUrlPattern', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('amphtml_enabled', $system);
        $this->assertStringContainsString('lazy_load_images', $system);

        $edit = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('meta_robots', $edit);
        $this->assertStringContainsString('NOINDEX,NOFOLLOW', $edit);

        $postSeo = file_get_contents($root . '/view/frontend/templates/post/seo.phtml');
        $this->assertStringContainsString('amphtml', $postSeo);

        $listTpl = file_get_contents($root . '/view/frontend/templates/article.phtml');
        $this->assertStringContainsString('loading="lazy"', $listTpl);
        $postTpl = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('loading="lazy"', $postTpl);

        $gql = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('meta_robots: String', $gql);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.27.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.27.0"', $composer);
    }
}
