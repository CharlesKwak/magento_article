<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class SpamNotifyFilesTest extends TestCase
{
    public function testSpamAndNotifyFilesExist(): void
    {
        $root = dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle';
        $this->assertFileExists($root . '/Model/CommentSpamGuard.php');
        $this->assertFileExists($root . '/Model/CommentNotifier.php');
        $this->assertFileExists($root . '/etc/email_templates.xml');
        $this->assertFileExists($root . '/view/frontend/email/comment_notification.html');
        $cfg = file_get_contents($root . '/etc/config.xml');
        $this->assertStringContainsString('spam_protection', $cfg);
        $this->assertStringContainsString('notify_enabled', $cfg);
    }
    public function testModuleVersionIs220(): void
    {
        $c = file_get_contents(dirname(__DIR__) . '/app/code/ThirdParty/BlogArticle/etc/module.xml');
        $this->assertStringContainsString('setup_version="2.7.0"', $c);
    }
}
