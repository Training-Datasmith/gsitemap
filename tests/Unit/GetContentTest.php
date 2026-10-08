<?php

namespace GsitemapTests\Unit;

use GsitemapTests\Support\RedirectException;
use GsitemapTests\TestCase;

class GetContentTest extends TestCase
{
    public function testSubmitSavesFrequencyAndExcludesEverySelectedMeta()
    {
        $this->pdo->exec("INSERT INTO ps_meta (id_meta, page, configurable) VALUES (5,'contact',1),(6,'prices-drop',1)");
        \Tools::setValue('SubmitGsitemap', '1');
        \Tools::setValue('gsitemap_frequency', 'daily');
        \Tools::setValue('gsitemap_meta', array('5', '6'));
        $module = $this->makeModule();
        $this->pdo->exec("INSERT INTO ps_gsitemap_sitemap (link, id_shop) VALUES ('old.xml',1)");
        $caught = null;
        try {
            $module->getContent();
        } catch (RedirectException $e) {
            $caught = $e;
        }
        $this->assertInstanceOf('GsitemapTests\Support\RedirectException', $caught);
        $this->assertSame('daily', \Configuration::get('GSITEMAP_FREQUENCY'));
        $links = array();
        $linkSitemap = array();
        $index = 0;
        $i = 0;
        $module->exposeGetMetaLink($linkSitemap, $this->langEn(), $index, $i);
        foreach ($linkSitemap as $row) {
            $links[] = $row['page'];
        }
        $this->assertNotContains('contact', $links);
        $this->assertNotContains('prices-drop', $links);
        $count = $this->pdo->query('SELECT COUNT(*) FROM ps_gsitemap_sitemap WHERE id_shop=1 AND link=\'old.xml\'')->fetchColumn();
        $this->assertSame('0', $count);
    }
}
