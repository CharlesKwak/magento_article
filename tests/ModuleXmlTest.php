<?php
use PHPUnit\Framework\TestCase;

class ModuleXmlTest extends TestCase
{
    public function testModuleXmlExistsAndContainsModuleName()
    {
        $path = __DIR__ . '/../app/code/ThirdParty/BlogArticle/etc/module.xml';
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertStringContainsString('ThirdParty_BlogArticle', $content);
    }
}
