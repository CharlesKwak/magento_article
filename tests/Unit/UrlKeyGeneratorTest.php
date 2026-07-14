<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ThirdParty\BlogArticle\Model\ResourceModel\Post\CollectionFactory;
use ThirdParty\BlogArticle\Model\UrlKeyGenerator;

class UrlKeyGeneratorTest extends TestCase
{
    public function testSlugifyProducesUrlSafeKey(): void
    {
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $generator = new UrlKeyGenerator($collectionFactory);

        $method = new ReflectionMethod(UrlKeyGenerator::class, 'slugify');
        // ReflectionMethod::setAccessible() is a no-op since PHP 8.1; omit for 8.5+.

        $this->assertSame('welcome-to-the-blog', $method->invoke($generator, 'Welcome to the blog'));
        $this->assertSame('hello-world', $method->invoke($generator, '  Hello___World!! '));
        $this->assertSame('', $method->invoke($generator, '***'));
    }
}
