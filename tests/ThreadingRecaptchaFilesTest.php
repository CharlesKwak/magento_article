<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ThreadingRecaptchaFilesTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
    }

    public function testThreadingAndRecaptchaFilesExist(): void
    {
        $root = $this->root();
        foreach ([
            '/Setup/Patch/Schema/AddCommentParentId.php',
            '/Model/RecaptchaValidator.php',
            '/Api/Data/CommentInterface.php',
            '/Model/Data/Comment.php',
            '/Model/CommentRepository.php',
            '/Model/Config.php',
            '/Controller/Comment/Post.php',
            '/Block/Post/View.php',
            '/view/frontend/templates/post/view.phtml',
            '/etc/adminhtml/system.xml',
            '/etc/config.xml',
            '/etc/schema.graphqls',
            '/Model/Resolver/SubmitBlogComment.php',
            '/Model/Resolver/PostComments.php',
            '/view/adminhtml/ui_component/blogarticle_comment_listing.xml',
        ] as $rel) {
            $this->assertFileExists($root . $rel, $rel);
        }
    }

    public function testParentIdAndRecaptchaWiring(): void
    {
        $root = $this->root();

        $interface = file_get_contents($root . '/Api/Data/CommentInterface.php');
        $this->assertStringContainsString('PARENT_ID', $interface);
        $this->assertStringContainsString('getParentId', $interface);

        $repo = file_get_contents($root . '/Model/CommentRepository.php');
        $this->assertStringContainsString('parent_id', $repo);
        $this->assertStringContainsString('Invalid parent comment', $repo);

        $patch = file_get_contents($root . '/Setup/Patch/Schema/AddCommentParentId.php');
        $this->assertStringContainsString("'parent_id'", $patch);

        $validator = file_get_contents($root . '/Model/RecaptchaValidator.php');
        $this->assertStringContainsString('siteverify', $validator);
        $this->assertStringContainsString('assertValid', $validator);

        $config = file_get_contents($root . '/Model/Config.php');
        $this->assertStringContainsString('isRecaptchaEnabled', $config);
        $this->assertStringContainsString('getRecaptchaSecretKey', $config);

        $system = file_get_contents($root . '/etc/adminhtml/system.xml');
        $this->assertStringContainsString('recaptcha_enabled', $system);
        $this->assertStringContainsString('recaptcha_site_key', $system);
        $this->assertStringContainsString('recaptcha_secret_key', $system);

        $cfgXml = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('recaptcha_enabled', $cfgXml);

        $schema = file_get_contents($root . '/etc/schema.graphqls');
        $this->assertStringContainsString('parent_id', $schema);
        $this->assertStringContainsString('recaptcha_token', $schema);

        $submit = file_get_contents($root . '/Model/Resolver/SubmitBlogComment.php');
        $this->assertStringContainsString('RecaptchaValidator', $submit);
        $this->assertStringContainsString('parent_id', $submit);

        $postComments = file_get_contents($root . '/Model/Resolver/PostComments.php');
        $this->assertStringContainsString('parent_id', $postComments);

        $controller = file_get_contents($root . '/Controller/Comment/Post.php');
        $this->assertStringContainsString('RecaptchaValidator', $controller);
        $this->assertStringContainsString('parent_id', $controller);

        $view = file_get_contents($root . '/Block/Post/View.php');
        $this->assertStringContainsString('getRootComments', $view);
        $this->assertStringContainsString('getReplyComments', $view);
        $this->assertStringContainsString('isRecaptchaEnabled', $view);

        $phtml = file_get_contents($root . '/view/frontend/templates/post/view.phtml');
        $this->assertStringContainsString('parent_id', $phtml);
        $this->assertStringContainsString('g-recaptcha', $phtml);
        $this->assertStringContainsString('blog-comment-reply', $phtml);

        $listing = file_get_contents($root . '/view/adminhtml/ui_component/blogarticle_comment_listing.xml');
        $this->assertStringContainsString('parent_id', $listing);
    }

    public function testModuleVersionIs290(): void
    {
        $c = file_get_contents($this->root() . '/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.20.0"', $c);
        $composer = file_get_contents(dirname(__DIR__) . '/composer.json');
        $this->assertStringContainsString('"version": "2.20.0"', $composer);
    }
}
