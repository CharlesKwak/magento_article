<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CategoryFilesTest extends TestCase
{
    public function testCategoryStackExists(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        foreach ([
            '/Model/Category.php',
            '/Model/CategoryRepository.php',
            '/Setup/Patch/Schema/AddCategorySupport.php',
            '/Setup/Patch/Data/AddDefaultCategory.php',
            '/Controller/Adminhtml/Category/Index.php',
            '/Api/CategoryRepositoryInterface.php',
            '/etc/schema.graphqls',
        ] as $rel) {
            $this->assertFileExists($root . $rel, 'Missing ' . $rel);
        }
    }

    public function testModuleVersionIs150(): void
    {
        $content = file_get_contents(
            dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml'
        );
        $this->assertStringContainsString('setup_version="1.9.0"', $content);
    }
}
