<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RelatedAndMediaFilesTest extends TestCase
{
    public function testRelatedAndMediaFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        foreach ([
            '/Setup/Patch/Schema/AddFeaturedImageAndMeta.php',
            '/Model/Resolver/RelatedPosts.php',
            '/Block/Post/View.php',
        ] as $rel) {
            $this->assertFileExists($root . $rel);
        }
        $schema = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('related_posts', $schema);
        $this->assertStringContainsString('featured_image', $schema);
        $install = file_get_contents($root . '/Setup/InstallSchema.php');
        $this->assertStringContainsString('featured_image', $install);
        $this->assertStringContainsString('meta_title', $install);
        $this->assertStringContainsString('thirdparty_blogarticle_post', $install);
        // Featured image is defined on the post table (InstallSchema creates full baseline).
        $this->assertNotFalse(strpos($install, 'featured_image'));
    }

    public function testModuleVersionIs170(): void
    {
        $content = file_get_contents(
            dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml'
        );
        $this->assertStringContainsString('setup_version="2.29.0"', $content);
    }
}
