<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ThirdParty\BlogArticle\Model\Import\WordPressCsvMapper;

class WordPressCsvMapperTest extends TestCase
{
    private WordPressCsvMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new WordPressCsvMapper();
    }

    public function testMapsWordpressHeaders(): void
    {
        $headers = $this->mapper->mapHeaders([
            'post_title',
            'post_content',
            'post_name',
            'post_author',
            'post_status',
            'post_date_gmt',
            '_yoast_wpseo_title',
        ]);
        $this->assertSame(
            ['title', 'content', 'url_key', 'author', 'status', 'published_at', 'meta_title'],
            $headers
        );
    }

    public function testNormalizesStatusAndPermalink(): void
    {
        $row = $this->mapper->normalizeRow([
            'title' => 'Hello',
            'content' => '<p>x</p>',
            'status' => 'publish',
            'url_key' => 'https://example.com/blog/my-post/',
            'published_at' => '2024-06-01 12:00:00',
        ]);
        $this->assertSame('enabled', $row['status']);
        $this->assertSame('my-post', $row['url_key']);
        $this->assertNotSame('', $row['published_at']);
    }

    public function testDraftBecomesDisabled(): void
    {
        $row = $this->mapper->normalizeRow(['status' => 'draft']);
        $this->assertSame('disabled', $row['status']);
    }
}
