<?php

namespace GsitemapTests\Unit;

use GsitemapTests\TestCase;

class CronControllerTest extends TestCase
{
    public function testRejectsBadTokenWithMarkers()
    {
        $markerDir = sys_get_temp_dir() . '/gsitemap-markers-' . uniqid('', true);
        mkdir($markerDir, 0777, true);
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-cron-token.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script) . ' bad ' . escapeshellarg($markerDir), $out, $code);
        $this->assertSame(0, $code);
        $this->assertFileExists($markerDir . '/started');
        $this->assertFileNotExists($markerDir . '/emptied');
        $this->assertFileNotExists($markerDir . '/generated');
        $this->removeTree($markerDir);
    }

    public function testCliSkipsToken()
    {
        $markerDir = sys_get_temp_dir() . '/gsitemap-markers-' . uniqid('', true);
        mkdir($markerDir, 0777, true);
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-cron-token.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script) . ' cli ' . escapeshellarg($markerDir), $out, $code);
        $this->assertFileExists($markerDir . '/generated');
        $this->removeTree($markerDir);
    }

    public function testSelectsRequestedShop()
    {
        $markerDir = sys_get_temp_dir() . '/gsitemap-markers-' . uniqid('', true);
        mkdir($markerDir, 0777, true);
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-cron-token.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script) . ' shop2 ' . escapeshellarg($markerDir), $out, $code);
        $payload = json_decode(file_get_contents($markerDir . '/shop.json'), true);
        $this->assertSame(2, $payload['shop']);
        $this->removeTree($markerDir);
    }

    public function testUnknownShopFallsBackToDefault()
    {
        $markerDir = sys_get_temp_dir() . '/gsitemap-markers-' . uniqid('', true);
        mkdir($markerDir, 0777, true);
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-cron-token.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script) . ' shop99 ' . escapeshellarg($markerDir), $out, $code);
        $payload = json_decode(file_get_contents($markerDir . '/shop.json'), true);
        $this->assertSame(1, $payload['shop']);
        $this->removeTree($markerDir);
    }

    public function testContinueDoesNotEmpty()
    {
        $markerDir = sys_get_temp_dir() . '/gsitemap-markers-' . uniqid('', true);
        mkdir($markerDir, 0777, true);
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-cron-token.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script) . ' continue ' . escapeshellarg($markerDir), $out, $code);
        $this->assertFileNotExists($markerDir . '/emptied');
        $this->assertFileExists($markerDir . '/generated');
        $this->removeTree($markerDir);
    }
}
