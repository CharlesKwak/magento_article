<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CliCreateDeleteMassTaxonomyFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Controller/Adminhtml/Category/MassEnable.php',
            '/Controller/Adminhtml/Category/MassDisable.php',
            '/Controller/Adminhtml/Tag/MassEnable.php',
            '/Controller/Adminhtml/Tag/MassDisable.php',
            '/Console/Command/PostCreateCommand.php',
            '/Console/Command/PostDeleteCommand.php',
            '/view/adminhtml/ui_component/blogarticle_category_listing.xml',
            '/view/adminhtml/ui_component/blogarticle_tag_listing.xml',
            '/etc/di.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testWiring(): void
    {
        $root = $this->root();

        $catListing = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_category_listing.xml');
        $this->assertStringContainsString('massEnable', $catListing);
        $this->assertStringContainsString('massDisable', $catListing);

        $tagListing = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_tag_listing.xml');
        $this->assertStringContainsString('massEnable', $tagListing);
        $this->assertStringContainsString('massDisable', $tagListing);

        $create = file_get_contents($root . '/Console/Command/PostCreateCommand.php');
        $this->assertStringContainsString('blogarticle:post:create', $create);
        $this->assertStringContainsString('--title', $create);

        $delete = file_get_contents($root . '/Console/Command/PostDeleteCommand.php');
        $this->assertStringContainsString('blogarticle:post:delete', $delete);
        $this->assertStringContainsString('--force', $delete);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('PostCreateCommand', $di);
        $this->assertStringContainsString('PostDeleteCommand', $di);
    }

    public function testModuleVersionIs290(): void
    {
        $c = file_get_contents($this->root() . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.33.0"', $c);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.33.0"', $composer);
    }
}
