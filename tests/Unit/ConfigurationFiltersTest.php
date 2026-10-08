<?php

namespace GsitemapTests\Unit;

use GsitemapTests\TestCase;

class ConfigurationFiltersTest extends TestCase
{
    public function testDisabledMetasEmpty()
    {
        $module = $this->makeModule();
        $this->assertSame(array(), $module->exposeGetDisabledMetas());
        \Configuration::set('GSITEMAP_DISABLE_LINKS', '');
        $this->assertSame(array(), $module->exposeGetDisabledMetas());
    }

    public function testDisabledMetasSplitsBareCommaList()
    {
        \Configuration::set('GSITEMAP_DISABLE_LINKS', '3,4');
        $module = $this->makeModule();
        $this->assertSame(array('3', '4'), $module->exposeGetDisabledMetas());
    }

    public function testMetasForConfigurationFiltersAutomaticAndBlockedPages()
    {
        $this->insertMetaRows(array(
            array('id_meta' => 1, 'page' => 'index'),
            array('id_meta' => 2, 'page' => 'cart'),
            array('id_meta' => 3, 'page' => 'best-sales'),
            array('id_meta' => 4, 'page' => 'manufacturer'),
            array('id_meta' => 5, 'page' => 'supplier'),
            array('id_meta' => 6, 'page' => 'contact'),
            array('id_meta' => 7, 'page' => 'prices-drop'),
        ));
        $module = $this->makeModule();
        $pages = array();
        foreach ($module->exposeGetMetasForConfiguration() as $meta) {
            $pages[] = $meta['page'];
        }
        $this->assertSame(array('contact', 'prices-drop'), $pages);
    }

    public function testListingFlagsOn178()
    {
        $module = $this->makeModule();
        \Configuration::set('PS_DISPLAY_MANUFACTURERS', 1);
        \Configuration::set('PS_DISPLAY_SUPPLIERS', 0);
        \Configuration::set('PS_DISPLAY_BEST_SELLERS', 1);
        $this->assertTrue($module->exposeIsManufacturerListingEnabled());
        $this->assertFalse($module->exposeIsSupplierListingEnabled());
        $this->assertTrue($module->exposeIsBestSellersListingEnabled());
    }

    public function testListingFlagsOn176ChildProcess()
    {
        $php = PHP_BINARY;
        $script = __DIR__ . '/../bin/run-listing-176.php';
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script);
        exec($cmd, $out, $code);
        $this->assertSame(0, $code);
        $this->assertContains('OK', implode("\n", $out));
    }
}
