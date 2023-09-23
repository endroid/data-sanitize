<?php

declare(strict_types=1);

namespace Endroid\DataSanitize\Tests;

use PHPUnit\Framework\TestCase;

final class SanitizerTest extends TestCase
{
    public function testNoTestsYet(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__DIR__.'/../src'),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $files = [];
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            include_once $file->getPathname();
        }

        $this->assertTrue(true);
    }
}
