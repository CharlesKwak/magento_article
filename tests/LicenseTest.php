<?php
use PHPUnit\Framework\TestCase;

class LicenseTest extends TestCase
{
    public function testLicenseFileExistsAndNotEmpty()
    {
        $licensePath = __DIR__ . '/../LICENSE';
        $this->assertFileExists($licensePath);
        $this->assertNotEmpty(trim(file_get_contents($licensePath)));
    }
}
