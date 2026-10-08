<?php

namespace GsitemapTests\Unit;

use GsitemapTests\TestCase;

class ConstructorAndHelpersTest extends TestCase
{
    public function testConstructorIdentity()
    {
        $module = $this->makeModule();
        $this->assertSame('gsitemap', $module->name);
        $this->assertSame('5.0.0', $module->version);
        $this->assertSame('checkout', $module->tab);
        $this->assertSame('gSitemapAppendUrls', \Gsitemap::HOOK_ADD_URLS);
        $this->assertSame('1.7.1.0', $module->ps_versions_compliancy['min']);
    }

    public function testNormalizeDirectoryAppendsSeparator()
    {
        $module = $this->makeModule();
        $this->assertSame('/var/www/', $module->exposeNormalizeDirectory('/var/www'));
        $this->assertSame('/var/www/', $module->exposeNormalizeDirectory('/var/www/'));
        $sep = DIRECTORY_SEPARATOR;
        $this->assertSame('/var/www' . $sep, $module->exposeNormalizeDirectory('/var/www\\'));
    }

    public function testRemoveControlCharactersStripsNulAndCollapsesWhitespace()
    {
        $module = $this->makeModule();
        $out = $module->exposeRemoveControlCharacters("a\x00b\n\tc");
        $this->assertInternalType('string', $out);
        $this->assertNotSame('', $out);
        $this->assertSame('a b c', $out);
        $hello = $module->exposeRemoveControlCharacters('hello');
        $this->assertInternalType('string', $hello);
        $this->assertSame('hello', $hello);
    }

    public function testPriorityUsesConfiguredValue()
    {
        \Configuration::set('GSITEMAP_PRIORITY_PRODUCT', 0.9);
        $module = $this->makeModule();
        $this->assertSame(0.9, $module->exposeGetPriorityPage('product'));
    }

    public function testPriorityFallsBackForUnknownPage()
    {
        $module = $this->makeModule();
        $this->assertSame(0.1, $module->exposeGetPriorityPage('module'));
    }
}
