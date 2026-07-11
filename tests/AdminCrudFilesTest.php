<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class AdminCrudFilesTest extends TestCase
{
    public function testAdminCrudAndDataPatchFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $paths = [
            $root . '/Controller/Adminhtml/Post/NewAction.php',
            $root . '/Controller/Adminhtml/Post/Edit.php',
            $root . '/Controller/Adminhtml/Post/Save.php',
            $root . '/Controller/Adminhtml/Post/Delete.php',
            $root . '/Block/Adminhtml/Post/Edit.php',
            $root . '/view/adminhtml/layout/blogarticle_post_edit.xml',
            $root . '/view/adminhtml/templates/post/edit.phtml',
            $root . '/Setup/Patch/Data/AddSampleBlogPosts.php',
            $root . '/Model/PostFactory.php',
        ];

        foreach ($paths as $path) {
            $this->assertFileExists($path, 'Missing: ' . $path);
        }
    }

    public function testModuleVersionIs110(): void
    {
        $path = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml';
        $content = file_get_contents($path);
        $this->assertNotFalse($content);
        $this->assertStringContainsString('setup_version="1.1.0"', $content);
    }
}
