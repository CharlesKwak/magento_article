<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class DummyDataInputTest extends TestCase
{
    public function testDummyDataFileCanBeReadAndContainsRequiredKeys(): void
    {
        $dummyDataFile = getenv('DUMMY_DATA_FILE');

        if ($dummyDataFile === false || $dummyDataFile === '') {
            $this->markTestSkipped('Set DUMMY_DATA_FILE to run dummy data input test.');
        }
        $this->assertFileExists($dummyDataFile);

        $json = file_get_contents($dummyDataFile);
        $this->assertNotFalse($json, 'Failed to read dummy data file.');

        $data = json_decode($json, true);
        $this->assertIsArray($data, 'Dummy data file must contain a JSON array.');
        $this->assertNotEmpty($data, 'Dummy data array must not be empty.');

        foreach ($data as $index => $post) {
            $this->assertIsArray($post, sprintf('Item %d must be an object.', $index));
            $this->assertArrayHasKey('title', $post);
            $this->assertArrayHasKey('content', $post);
            $this->assertNotSame('', trim((string) $post['title']));
            $this->assertNotSame('', trim((string) $post['content']));
        }
    }
}
