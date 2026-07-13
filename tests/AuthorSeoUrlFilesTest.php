<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class AuthorSeoUrlFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testAuthorSeoAndCleanUrlFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Setup/Patch/Schema/AddAuthorColumn.php',
            '/Api/Data/PostInterface.php',
            '/Model/Data/Post.php',
            '/Model/PostRepository.php',
            '/Controller/Router.php',
            '/Controller/Index/Index.php',
            '/Block/Article.php',
            '/Block/Post/View.php',
            '/view/frontend/templates/post/seo.phtml',
            '/view/frontend/layout/blog_post_view.xml',
            '/view/frontend/templates/post/view.phtml',
            '/view/frontend/templates/article.phtml',
            '/view/adminhtml/templates/post/edit.phtml',
            '/etc/schema.graphqls',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testAuthorAndSeoWiring(): void
    {
        $root = $this->root();

        $interface = file_get_contents($root . '/Api/Data/PostInterface.php');
        $this->assertStringContainsString('AUTHOR', $interface);
        $this->assertStringContainsString('getAuthor', $interface);

        $patch = file_get_contents($root . '/Setup/Patch/Schema/AddAuthorColumn.php');
        $this->assertStringContainsString("'author'", $patch);

        $repo = file_get_contents($root . '/Model/PostRepository.php');
        $this->assertStringContainsString('setAuthor', $repo);

        $router = file_get_contents($root . '/Controller/Router.php');
        $this->assertStringContainsString('blog/category/', $router);
        $this->assertStringContainsString('blog/tag/', $router);
        $this->assertStringContainsString("'category'", $router);
        $this->assertStringContainsString("'tag'", $router);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('blog/category/', $article);
        $this->assertStringContainsString('getFilterHeading', $article);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('getJsonLd', $view);
        $this->assertStringContainsString('getSocialMetaTags', $view);
        $this->assertStringContainsString('getAuthorName', $view);
        // OG tags are built in the block (not hardcoded in the template).
        $this->assertStringContainsString('og:', $view);
        $this->assertStringContainsString("og:title", $view);

        $seo = file_get_contents($root . '/view/frontend/templates/post/seo.phtml');
        $this->assertStringContainsString('application/ld+json', $seo);
        $this->assertStringContainsString('getSocialMetaTags', $seo);
        $this->assertStringContainsString('getJsonLd', $seo);

        $layout = file_get_contents($root . '/view/frontend/layout/blog_post_view.xml');
        $this->assertStringContainsString('blog.post.seo', $layout);
        $this->assertStringContainsString('head.additional', $layout);

        $schema = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('author: String', $schema);

        $mapper = file_get_contents($root . '/Model/Resolver/DataMapper/PostMapper.php');
        $this->assertStringContainsString("'author'", $mapper);

        $edit = file_get_contents($root . '/view/adminhtml/templates/post/edit.phtml');
        $this->assertStringContainsString('name="author"', $edit);
    }

    public function testModuleVersionIs290(): void
    {
        $c = file_get_contents($this->root() . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.28.0"', $c);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.28.0"', $composer);
    }
}
