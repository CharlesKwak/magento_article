<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SeoStructuredDataFilesTest extends TestCase
{
    public function testListSeoAndRelatedLimitWiring(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/view/frontend/templates/list/seo.phtml');

        $layout = file_get_contents($root . '/view/frontend/layout/blog_index_index.xml');
        $this->assertStringContainsString('blog.list.seo', $layout);
        $this->assertStringContainsString('list/seo.phtml', $layout);

        $article = file_get_contents($root . '/Block/Article.php');
        $this->assertStringContainsString('getSocialMetaTags', $article);
        $this->assertStringContainsString('getJsonLd', $article);
        $this->assertStringContainsString('CollectionPage', $article);
        $this->assertStringContainsString('BreadcrumbList', $article);
        $this->assertStringContainsString('og:type', $article);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('BreadcrumbList', $view);
        $this->assertStringContainsString('@graph', $view);

        $schema = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('related_posts(limit: Int = 3)', $schema);

        $related = file_get_contents($root . '/Model/Resolver/RelatedPosts.php');
        $this->assertStringContainsString("\$args['limit']", $related);
        $this->assertStringContainsString('PostMapper', $related);
        $this->assertStringContainsString('min(20', $related);

        $module = file_get_contents($root . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.32.0"', $module);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.32.0"', $composer);
    }
}
