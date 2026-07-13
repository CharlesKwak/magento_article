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
            $root . '/Setup/Patch/Data/BackfillUrlKeysAndStatus.php',
            $root . '/Setup/Patch/Schema/AddUrlKeyAndStatusColumns.php',
            $root . '/Model/PostFactory.php',
            $root . '/Model/UrlKeyGenerator.php',
            $root . '/Controller/Post/View.php',
            $root . '/Block/Post/View.php',
            $root . '/view/frontend/layout/blog_post_view.xml',
            $root . '/view/frontend/templates/post/view.phtml',
            $root . '/etc/adminhtml/di.xml',
        ];

        foreach ($paths as $path) {
            $this->assertFileExists($path, 'Missing: ' . $path);
        }
    }

    public function testModuleVersionIsCurrent(): void
    {
        $path = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml';
        $content = file_get_contents($path);
        $this->assertNotFalse($content);
        $this->assertStringContainsString('setup_version="2.33.0"', $content);
    }

    public function testInstallSchemaDefinesUrlKeyAndStatus(): void
    {
        $path = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/Setup/InstallSchema.php';
        $content = file_get_contents($path);
        $this->assertNotFalse($content);
        $this->assertStringContainsString("'url_key'", $content);
        $this->assertStringContainsString("'is_active'", $content);
    }
}
