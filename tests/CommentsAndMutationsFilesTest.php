<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class CommentsAndMutationsFilesTest extends TestCase
{
    public function testCommentAndMutationFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        foreach ([
            '/Setup/Patch/Schema/AddCommentSupport.php',
            '/Model/CommentRepository.php',
            '/Controller/Comment/Post.php',
            '/Controller/Adminhtml/Comment/Index.php',
            '/Model/Resolver/CreateBlogPost.php',
            '/Model/Resolver/SubmitBlogComment.php',
            '/view/adminhtml/ui_component/blogarticle_comment_listing.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
        $schema = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('createBlogPost', $schema);
        $this->assertStringContainsString('submitBlogComment', $schema);
    }
    public function testModuleVersionIs210(): void
    {
        $c = file_get_contents(dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.10.0"', $c);
    }
}
