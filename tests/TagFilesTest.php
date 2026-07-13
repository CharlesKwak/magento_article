<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class TagFilesTest extends TestCase
{
    public function testTagStackExists(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        foreach ([
            '/Model/Tag.php',
            '/Model/TagRepository.php',
            '/Model/PostTagLink.php',
            '/Setup/Patch/Schema/AddTagSupport.php',
            '/Setup/Patch/Data/AddSampleTags.php',
            '/Controller/Adminhtml/Tag/Index.php',
            '/Api/TagRepositoryInterface.php',
            '/Model/Resolver/Tags.php',
        ] as $rel) {
            $this->assertFileExists($root . $rel, 'Missing ' . $rel);
        }
    }

    public function testModuleVersionIs160(): void
    {
        $content = file_get_contents(
            dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml'
        );
        $this->assertStringContainsString('setup_version="2.12.0"', $content);
    }
}
