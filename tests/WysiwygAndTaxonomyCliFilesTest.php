<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WysiwygAndTaxonomyCliFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Block/Adminhtml/Post/Edit.php',
            '/view/adminhtml/templates/post/edit.phtml',
            '/view/adminhtml/layout/blogarticle_post_edit.xml',
            '/Console/Command/CategoryListCommand.php',
            '/Console/Command/CategoryCreateCommand.php',
            '/Console/Command/CategorySetStatusCommand.php',
            '/Console/Command/TagListCommand.php',
            '/Console/Command/TagCreateCommand.php',
            '/Console/Command/TagSetStatusCommand.php',
            '/etc/di.xml',
            '/etc/module.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testWiring(): void
    {
        $root = $this->root();

        $edit = file_get_contents($root . '/Block/Adminhtml/Post/Edit.php');
        $this->assertStringContainsString('WysiwygConfig', $edit);
        $this->assertStringContainsString('getWysiwygConfigJson', $edit);

        $phtml = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('wysiwygSetup', $phtml);
        $this->assertStringContainsString('getWysiwygConfigJson', $phtml);

        $layout = file_get_contents($root . '/view/adminhtml/layout/blogarticle_post_edit.xml');
        $this->assertStringContainsString('handle="editor"', $layout);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('Magento_Cms', $module);
        $this->assertStringContainsString('setup_version="2.30.0"', $module);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('CategoryListCommand', $di);
        $this->assertStringContainsString('CategoryCreateCommand', $di);
        $this->assertStringContainsString('CategorySetStatusCommand', $di);
        $this->assertStringContainsString('TagListCommand', $di);
        $this->assertStringContainsString('TagCreateCommand', $di);
        $this->assertStringContainsString('TagSetStatusCommand', $di);

        $catCreate = file_get_contents($root . '/Console/Command/CategoryCreateCommand.php');
        $this->assertStringContainsString('blogarticle:category:create', $catCreate);

        $tagList = file_get_contents($root . '/Console/Command/TagListCommand.php');
        $this->assertStringContainsString('blogarticle:tag:list', $tagList);
    }

    public function testModuleVersionIs290(): void
    {
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.30.0"', $composer);
    }
}
