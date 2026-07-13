<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class MultiStoreUiFilesTest extends TestCase
{
    public function testMultiStoreAndUiFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Setup/Patch/Schema/AddStoreId.php');
        $this->assertFileExists($root . '/view/adminhtml/ui_component/blogarticle_post_listing.xml');
        $this->assertFileExists($root . '/Ui/Component/Listing/Column/PostActions.php');
        $this->assertFileExists($root . '/Controller/Adminhtml/Post/MassDelete.php');
        $this->assertFileExists($root . '/Model/Sitemap/ItemProvider/Posts.php');
        $install = file_get_contents($root . '/Setup/InstallSchema.php');
        $this->assertStringContainsString('store_id', $install);
    }
    public function testModuleVersionIs190(): void
    {
        $c = file_get_contents(dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.15.0"', $c);
    }
}
