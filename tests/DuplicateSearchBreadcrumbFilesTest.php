<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class DuplicateSearchBreadcrumbFilesTest extends TestCase
{
    public function testDuplicateSearchBreadcrumbWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $dup = file_get_contents($root . '/Model/PostDuplicator.php');
        $this->assertStringContainsString('class PostDuplicator', $dup);
        $this->assertStringContainsString('setTagsForPost', $dup);
        $this->assertStringContainsString('replaceLinks', $dup);
        $this->assertStringContainsString('Copy of', $dup);

        $ctrl = file_get_contents($root . '/Controller/Adminhtml/Post/Duplicate.php');
        $this->assertStringContainsString('PostDuplicator', $ctrl);
        $this->assertStringContainsString('ADMIN_RESOURCE', $ctrl);

        $cli = file_get_contents($root . '/Console/Command/PostDuplicateCommand.php');
        $this->assertStringContainsString('blogarticle:post:duplicate', $cli);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('PostDuplicateCommand', $di);

        $actions = file_get_contents($root . '/Ui/Component/Listing/Column/PostActions.php');
        $this->assertStringContainsString('URL_PATH_DUPLICATE', $actions);
        $this->assertStringContainsString("'duplicate'", $actions);

        $edit = file_get_contents($root . '/Block/Adminhtml/Post/Edit.php');
        $this->assertStringContainsString('getDuplicateUrl', $edit);

        $tpl = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('getDuplicateUrl', $tpl);
        $this->assertStringContainsString('Duplicate', $tpl);

        $filter = file_get_contents($root . '/Model/PostFilter.php');
        $this->assertStringContainsString("'excerpt'", $filter);
        $this->assertStringContainsString("'author'", $filter);
        $this->assertStringContainsString("'meta_title'", $filter);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString("addCrumb(\n                'category'", $view)
            || $this->assertStringContainsString("'category'", $view);
        $this->assertStringContainsString('getCategoryName()', $view);
        $this->assertStringContainsString('getCategoryUrl()', $view);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.30.0"', $module);

        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.30.0"', $composer);
    }
}
