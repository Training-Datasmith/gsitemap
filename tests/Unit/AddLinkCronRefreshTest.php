<?php

namespace GsitemapTests\Unit;

use GsitemapTests\TestCase;

class AddLinkCronRefreshTest extends TestCase
{
    public function testCronChunkRefreshTargetsModuleFrontController()
    {
        $markerDir = sys_get_temp_dir() . '/gsitemap-refresh-' . uniqid('', true);
        mkdir($markerDir, 0777, true);
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-cron-refresh.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($markerDir), $out, $code);
        $this->assertFileExists($markerDir . '/module_link.json');
        $payload = json_decode(file_get_contents($markerDir . '/module_link.json'), true);
        $this->assertSame('gsitemap', $payload['module']);
        $this->assertSame('cron', $payload['controller']);
        $this->assertSame('1', $payload['params']['continue']);
        $this->assertSame('0123456789', $payload['params']['token']);
        $this->assertSame('product', $payload['params']['type']);
        $this->assertSame('en', $payload['params']['lang']);
        $this->assertSame(20, $payload['params']['index']);
        $this->assertSame(77, $payload['params']['id']);
        $this->assertSame(1, $payload['params']['id_shop']);
        $this->removeTree($markerDir);
    }
}
