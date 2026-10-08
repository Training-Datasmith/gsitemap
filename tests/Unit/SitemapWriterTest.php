<?php

namespace GsitemapTests\Unit;

use GsitemapTests\Support\XmlAssertions;
use GsitemapTests\TestCase;

class SitemapWriterTest extends TestCase
{
    use XmlAssertions;

    public function testNodeWritesLocPriorityChangefreqAndLastmod()
    {
        \Configuration::set('PS_REWRITING_SETTINGS', 0);
        $module = $this->makeModule();
        $path = $this->psRoot() . '/node.xml';
        $fd = fopen($path, 'wb');
        fwrite($fd, '<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url>');
        $module->exposeAddSitemapNode($fd, 'http://shop.example/p?a=1&b=2', 0.9, 'daily', '2020-06-15 13:45:00');
        fwrite($fd, '</url></urlset>');
        fclose($fd);
        list($dom, $xpath) = $this->loadUrlset($path);
        $loc = $xpath->evaluate('string(//sm:url/sm:loc)');
        $this->assertSame('http://shop.example/p?a=1&b=2', $loc);
        $this->assertSame('0.9', $xpath->evaluate('string(//sm:url/sm:priority)'));
        $this->assertSame('daily', $xpath->evaluate('string(//sm:url/sm:changefreq)'));
        $this->assertSame('2020-06-15T13:45:00+00:00', $xpath->evaluate('string(//sm:url/sm:lastmod)'));
    }

    public function testNodeUsesCdataWithoutDoubleEncodingWhenRewritingOn()
    {
        \Configuration::set('PS_REWRITING_SETTINGS', 1);
        $module = $this->makeModule();
        $index = 0;
        $links = array(
            array(
                'type' => 'home',
                'page' => 'home',
                'link' => 'http://shop.example/p?a=1&b=2',
                'image' => false,
            ),
        );
        $this->assertTrue($module->exposeRecursiveSitemapCreator($links, 'en', $index));
        list($dom, $xpath) = $this->loadUrlset($this->pathInRoot('1_en_0_sitemap.xml'));
        $loc = $xpath->evaluate('string(//sm:url/sm:loc)');
        $this->assertSame('http://shop.example/p?a=1&b=2', $loc);
    }

    public function testImageNode()
    {
        \Configuration::set('PS_REWRITING_SETTINGS', 0);
        $module = $this->makeModule();
        $path = $this->psRoot() . '/image.xml';
        $fd = fopen($path, 'wb');
        fwrite($fd, '<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"><url>');
        $module->exposeAddSitemapNodeImage($fd, 'http://shop.example/img/x.jpg');
        fwrite($fd, '</url></urlset>');
        fclose($fd);
        list($dom, $xpath) = $this->loadUrlset($path);
        $this->assertSame(
            'http://shop.example/img/x.jpg',
            $xpath->evaluate('string(//image:loc)')
        );
    }

    public function testRecursiveCreatorWritesChunkAndRecordsRow()
    {
        $module = $this->makeModule();
        $index = 0;
        $links = array(array('type' => 'home', 'page' => 'home', 'link' => 'http://shop.example/', 'image' => false));
        $this->assertTrue($module->exposeRecursiveSitemapCreator($links, 'en', $index));
        $this->assertSame(1, $index);
        $chunk = $this->pathInRoot('1_en_0_sitemap.xml');
        list($dom, $xpath) = $this->loadUrlset($chunk);
        $this->assertSame('http://shop.example/', $this->firstUrlLoc($xpath));
        $rows = $this->pdo->query('SELECT link FROM ps_gsitemap_sitemap WHERE id_shop=1')->fetchAll();
        $this->assertCount(1, $rows);
        $this->assertSame('1_en_0_sitemap.xml', $rows[0]['link']);
        $index = 0;
        $this->assertFalse($module->exposeRecursiveSitemapCreator(array(), 'en', $index));
    }

    public function testIndexSitemapListsOnlyThisShop()
    {
        $module = $this->makeModule();
        $this->pdo->exec("INSERT INTO ps_gsitemap_sitemap (link, id_shop) VALUES ('1_en_0_sitemap.xml', 1), ('2_en_0_sitemap.xml', 2)");
        $this->assertTrue($module->exposeCreateIndexSitemap());
        list($dom, $xpath) = $this->loadSitemapIndex($this->pathInRoot('1_index_sitemap.xml'));
        $locs = $this->indexLocs($xpath);
        $this->assertSame(array('http://shop.example/1_en_0_sitemap.xml'), $locs);
        $module2 = $this->makeModule();
        $this->pdo->exec('DELETE FROM ps_gsitemap_sitemap');
        $this->assertFalse($module2->exposeCreateIndexSitemap());
    }

    public function testChunkBoundaryFlushesWithoutAddingTheNewLink()
    {
        $module = $this->makeModule();
        $module->cron = false;
        $linkSitemap = array(array('type' => 'home', 'page' => 'home', 'link' => 'http://shop.example/old', 'image' => false));
        $index = 0;
        $i = 25001;
        $newLink = array('type' => 'product', 'page' => 'product', 'link' => 'http://shop.example/new', 'image' => false);
        $caught = null;
        try {
            $module->addLinkToSitemap($linkSitemap, $newLink, 'en', $index, $i, 99);
        } catch (\GsitemapTests\Support\RedirectException $e) {
            $caught = $e;
        }
        $this->assertInstanceOf('GsitemapTests\Support\RedirectException', $caught);
        $this->assertContains('continue=1', $caught->url);
        $this->assertContains('id=99', $caught->url);
        $chunk = $this->pathInRoot('1_en_0_sitemap.xml');
        list($dom, $xpath) = $this->loadUrlset($chunk);
        $locs = $this->urlLocs($xpath);
        $this->assertSame(array('http://shop.example/old'), $locs);
        $this->assertSame(1, $index);
        $i = 1;
        $linkSitemap = array();
        $this->assertTrue($module->addLinkToSitemap($linkSitemap, $newLink, 'en', $index, $i, 99));
    }

    public function testUnderLimitAddsWithoutFlush()
    {
        $module = $this->makeModule();
        $linkSitemap = array();
        $index = 0;
        $i = 1;
        $newLink = array('type' => 'home', 'page' => 'home', 'link' => 'http://shop.example/only', 'image' => false);
        $this->assertTrue($module->addLinkToSitemap($linkSitemap, $newLink, 'en', $index, $i, -1));
        $this->assertCount(1, $linkSitemap);
        $this->assertFileNotExists($this->pathInRoot('1_en_0_sitemap.xml'));
    }

    public function testMemoryCeilingFlushesChildProcess()
    {
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-memory-flush.php';
        exec(escapeshellarg($php) . ' ' . escapeshellarg($script), $out, $code);
        $this->assertSame(0, $code);
        $this->assertContains('OK', implode("\n", $out));
    }
}
