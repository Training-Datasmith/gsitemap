<?php

namespace GsitemapTests\Unit;

use GsitemapTests\Support\RedirectException;
use GsitemapTests\Support\XmlAssertions;
use GsitemapTests\TestCase;

class LifecycleTest extends TestCase
{
    use XmlAssertions;

    public function testInstallWritesEveryDefaultAndCreatesTableAndHook()
    {
        $this->clearGsitemapModuleInstallState();
        $module = $this->makeModule();
        $this->assertTrue($module->install());
        $this->assertSame(1.0, \Configuration::get('GSITEMAP_PRIORITY_HOME'));
        $this->assertSame(0.7, \Configuration::get('GSITEMAP_PRIORITY_SUPPLIER'));
        $this->assertSame('weekly', \Configuration::get('GSITEMAP_FREQUENCY'));
        $this->assertSame('', \Configuration::get('GSITEMAP_DISABLE_LINKS'));
        $this->assertTrue(array_key_exists('GSITEMAP_LAST_EXPORT', \Configuration::all()));
        $this->assertFalse(\Configuration::get('GSITEMAP_LAST_EXPORT'));
        $rows = $this->pdo->query("SHOW TABLES LIKE 'ps_gsitemap_sitemap'")->fetchAll();
        $this->assertNotEmpty($rows);
        $this->assertSame(1, \Hook::getIdByName('gSitemapAppendUrls'));
        $this->assertTrue($module->install());
    }

    public function testUninstallDeletesSupplierPriority()
    {
        $module = $this->makeModule();
        $this->assertTrue($module->install());
        $this->assertTrue($module->uninstall());
        $this->assertFalse(array_key_exists('GSITEMAP_PRIORITY_SUPPLIER', \Configuration::all()));
        $this->assertFalse(array_key_exists('GSITEMAP_PRIORITY_HOME', \Configuration::all()));
        $rows = $this->pdo->query("SHOW TABLES LIKE 'ps_gsitemap_sitemap'")->fetchAll();
        $this->assertEmpty($rows);
    }

    public function testEmptySitemapIsShopScoped()
    {
        file_put_contents($this->pathInRoot('s1.xml'), 'a');
        file_put_contents($this->pathInRoot('s2.xml'), 'b');
        $this->pdo->exec("INSERT INTO ps_gsitemap_sitemap (link, id_shop) VALUES ('s1.xml',1),('s2.xml',2)");
        $module = $this->makeModule();
        $this->assertTrue($module->emptySitemap(2));
        $this->assertFileExists($this->pathInRoot('s1.xml'));
        $this->assertFileNotExists($this->pathInRoot('s2.xml'));
        $count = $this->pdo->query('SELECT COUNT(*) FROM ps_gsitemap_sitemap WHERE id_shop=1')->fetchColumn();
        $this->assertSame('1', $count);
    }

    public function testRemoveSitemapDeletesExistingFilesAndDropsTable()
    {
        file_put_contents($this->pathInRoot('a.xml'), 'x');
        file_put_contents($this->pathInRoot('b.xml'), 'y');
        $this->pdo->exec("INSERT INTO ps_gsitemap_sitemap (link, id_shop) VALUES ('a.xml',1),('b.xml',2)");
        $module = $this->makeModule();
        $this->assertTrue($module->removeSitemap());
        $this->assertFileNotExists($this->pathInRoot('a.xml'));
        $this->assertFileNotExists($this->pathInRoot('b.xml'));
        $this->assertEmpty($this->pdo->query("SHOW TABLES LIKE 'ps_gsitemap_sitemap'")->fetchAll());
    }

    public function testCreateSitemapPermissionFailure()
    {
        $path = $this->psRoot();
        $this->removeTree($path);
        try {
            file_put_contents($path, 'x');
            $module = $this->makeModule();
            $this->assertFalse($module->createSitemap());
            $this->assertNotEmpty($module->context->controller->errors);
        } finally {
            if (file_exists($path) && !is_dir($path)) {
                unlink($path);
            }
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    public function testCreateSitemapWritesHomeChunkAndIndex()
    {
        \Configuration::set('GSITEMAP_LAST_EXPORT', 'sentinel');
        $module = $this->makeModule();
        $caught = null;
        try {
            $module->createSitemap();
        } catch (RedirectException $e) {
            $caught = $e;
        }
        $this->assertInstanceOf('GsitemapTests\Support\RedirectException', $caught);
        list($dom, $xpath) = $this->loadUrlset($this->pathInRoot('1_en_0_sitemap.xml'));
        $this->assertSame('http://shop.example/page/index?lang=1', $this->firstUrlLoc($xpath));
        list($idxDom, $idxXpath) = $this->loadSitemapIndex($this->pathInRoot('1_index_sitemap.xml'));
        $this->assertSame('http://shop.example/1_en_0_sitemap.xml', $this->indexLocs($idxXpath)[0]);
        $last = \Configuration::get('GSITEMAP_LAST_EXPORT');
        $this->assertNotSame('sentinel', $last);
        $this->assertNotFalse($last);
        $this->assertRegExp('/^[A-Za-z]{3}, /', $last);
        $this->assertFileNotExists($this->pathInRoot('test.txt'));
    }

    public function testCreateSitemapResumesAtTypeAndShop()
    {
        \Tools::setValue('type', 'product');
        \Tools::setValue('lang', 'en');
        \Tools::setValue('id', 5);
        \Tools::setValue('index', 2);
        \Tools::setValue('id_shop', 2);
        $module = $this->makeModule();
        $module->context->shop = new \Shop(2);
        $this->pdo->exec('INSERT INTO ps_shop (id_shop) VALUES (2) ON DUPLICATE KEY UPDATE id_shop=id_shop');
        $this->pdo->exec("INSERT INTO ps_product_shop (id_product, id_shop, active, visibility) VALUES (4,2,1,'both'),(5,2,1,'both'),(6,2,1,'both')");
        $caught = null;
        try {
            $module->createSitemap(2);
        } catch (RedirectException $e) {
            $caught = $e;
        }
        $this->assertInstanceOf('GsitemapTests\Support\RedirectException', $caught);
        $chunk = $this->pathInRoot('2_en_2_sitemap.xml');
        $this->assertFileExists($chunk);
        list($dom, $xpath) = $this->loadUrlset($chunk);
        $locs = $this->urlLocs($xpath);
        $this->assertContains('http://shop.example/product/5?lang=1', $locs);
        $this->assertNotContains('http://shop.example/product/4?lang=1', $locs);
    }

    public function testCreateSitemapCronExitsChildProcess()
    {
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-cron-exit.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script), $out, $code);
        $this->assertSame(0, $code);
        $this->assertFileExists($this->pathInRoot('1_index_sitemap.xml'));
    }
}
