<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CliAndMassStatusFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testMassStatusAndCliFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Controller/Adminhtml/Post/MassEnable.php',
            '/Controller/Adminhtml/Post/MassDisable.php',
            '/Controller/Adminhtml/Post/MassDelete.php',
            '/Console/Command/PostListCommand.php',
            '/Console/Command/PostShowCommand.php',
            '/Console/Command/PostSetStatusCommand.php',
            '/view/adminhtml/ui_component/blogarticle_post_listing.xml',
            '/etc/di.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testMassStatusAndCliWiring(): void
    {
        $root = $this->root();

        $listing = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_post_listing.xml');
        $this->assertStringContainsString('massEnable', $listing);
        $this->assertStringContainsString('massDisable', $listing);
        $this->assertStringContainsString('massDelete', $listing);

        $enable = file_get_contents($root . '/Controller/Adminhtml/Post/MassEnable.php');
        $this->assertStringContainsString('setIsActive(1)', $enable);

        $disable = file_get_contents($root . '/Controller/Adminhtml/Post/MassDisable.php');
        $this->assertStringContainsString('setIsActive(0)', $disable);

        $listCmd = file_get_contents($root . '/Console/Command/PostListCommand.php');
        $this->assertStringContainsString('blogarticle:post:list', $listCmd);

        $showCmd = file_get_contents($root . '/Console/Command/PostShowCommand.php');
        $this->assertStringContainsString('blogarticle:post:show', $showCmd);

        $statusCmd = file_get_contents($root . '/Console/Command/PostSetStatusCommand.php');
        $this->assertStringContainsString('blogarticle:post:set-status', $statusCmd);

        $di = file_get_contents($root . '/etc/di.xml');
        $this->assertStringContainsString('PostListCommand', $di);
        $this->assertStringContainsString('PostShowCommand', $di);
        $this->assertStringContainsString('PostSetStatusCommand', $di);
        $this->assertStringContainsString('Magento\\Framework\\Console\\CommandList', $di);
    }

    public function testModuleVersionIs290(): void
    {
        $c = file_get_contents($this->root() . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.14.0"', $c);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.14.0"', $composer);
    }
}
