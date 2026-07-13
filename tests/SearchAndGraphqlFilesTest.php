<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SearchAndGraphqlFilesTest extends TestCase
{
    public function testSearchGraphqlAndConfigFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $paths = [
            $root . '/Model/Config.php',
            $root . '/Model/PostFilter.php',
            $root . '/Model/Resolver/Posts.php',
            $root . '/Model/Resolver/Post.php',
            $root . '/etc/schema.graphqls',
            $root . '/etc/config.xml',
            $root . '/etc/adminhtml/system.xml',
        ];
        foreach ($paths as $path) {
            $this->assertFileExists($path, 'Missing: ' . $path);
        }
    }

    public function testGraphqlSchemaDefinesQueries(): void
    {
        $content = file_get_contents(
            dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/schema.graphqls'
        );
        $this->assertNotFalse($content);
        $this->assertStringContainsString('blogPosts', $content);
        $this->assertStringContainsString('blogPost', $content);
        $this->assertStringContainsString('BlogPostList', $content);
    }

    public function testModuleVersionIs140(): void
    {
        $content = file_get_contents(
            dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml'
        );
        $this->assertNotFalse($content);
        $this->assertStringContainsString('setup_version="2.12.0"', $content);
    }
}
