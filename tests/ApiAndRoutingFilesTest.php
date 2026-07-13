<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ApiAndRoutingFilesTest extends TestCase
{
    public function testApiAndRoutingFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';

        $paths = [
            $root . '/Api/PostRepositoryInterface.php',
            $root . '/Api/Data/PostInterface.php',
            $root . '/Model/PostRepository.php',
            $root . '/Model/Data/Post.php',
            $root . '/Controller/Router.php',
            $root . '/etc/webapi.xml',
            $root . '/etc/di.xml',
            $root . '/etc/frontend/di.xml',
        ];

        foreach ($paths as $path) {
            $this->assertFileExists($path, 'Missing: ' . $path);
        }
    }

    public function testWebapiDeclaresPublicGetRoutes(): void
    {
        $content = file_get_contents(
            dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/webapi.xml'
        );
        $this->assertNotFalse($content);
        $this->assertStringContainsString('/V1/blogarticle/posts', $content);
        $this->assertStringContainsString('getByUrlKey', $content);
        $this->assertStringContainsString('anonymous', $content);
    }

    public function testModuleVersionIsCurrent(): void
    {
        $content = file_get_contents(
            dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml'
        );
        $this->assertNotFalse($content);
        $this->assertStringContainsString('setup_version="2.19.0"', $content);
    }
}
