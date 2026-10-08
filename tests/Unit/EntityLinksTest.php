<?php

namespace GsitemapTests\Unit;

use GsitemapTests\Support\XmlAssertions;
use GsitemapTests\TestCase;

class EntityLinksTest extends TestCase
{
    use XmlAssertions;

    private function collectViaHome($module, $method, $extraArgs = array())
    {
        $linkSitemap = array();
        $index = 0;
        $i = 0;
        $lang = $this->langEn();
        $args = array(&$linkSitemap, $lang, &$index, &$i);
        foreach ($extraArgs as $arg) {
            $args[] = $arg;
        }
        call_user_func_array(array($module, $method), $args);

        return $linkSitemap;
    }

    public function testHomeLink()
    {
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetHomeLink');
        $this->assertCount(1, $links);
        $this->assertSame('home', $links[0]['page']);
        $this->assertSame('http://shop.example/page/index?lang=1', $links[0]['link']);
    }

    public function testMetaLinkSkipsBlockedDisabledAndToggledPages()
    {
        $this->pdo->exec("INSERT INTO ps_meta (id_meta, page, configurable) VALUES
            (1,'index',1),(2,'cart',1),(3,'contact',1),(4,'prices-drop',1),(5,'best-sales',1),(6,'manufacturer',1),(7,'supplier',1)");
        \Configuration::set('GSITEMAP_DISABLE_LINKS', '4');
        \Configuration::set('PS_DISPLAY_BEST_SELLERS', 0);
        \Configuration::set('PS_DISPLAY_MANUFACTURERS', 0);
        \Configuration::set('PS_DISPLAY_SUPPLIERS', 0);
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetMetaLink');
        $pages = array();
        foreach ($links as $link) {
            $pages[] = $link['page'];
        }
        $this->assertSame(array('contact'), $pages);
        \Configuration::set('PS_DISPLAY_BEST_SELLERS', 1);
        $links = $this->collectViaHome($module, 'exposeGetMetaLink');
        $pages = array();
        foreach ($links as $link) {
            $pages[] = $link['page'];
        }
        $this->assertContains('best-sales', $pages);
    }

    public function testProductLinkFiltersActiveVisibleShopAndResumeId()
    {
        $this->pdo->exec("INSERT INTO ps_product_shop (id_product, id_shop, active, visibility) VALUES
            (3,1,1,'both'),(4,1,1,'both'),(5,1,1,'both'),(6,1,0,'both'),(7,1,1,'none'),(8,2,1,'both')");
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetProductLink', array(4));
        $locs = array();
        foreach ($links as $link) {
            $locs[] = $link['link'];
        }
        $this->assertContains('http://shop.example/product/4?lang=1', $locs);
        $this->assertContains('http://shop.example/product/5?lang=1', $locs);
        $this->assertNotContains('http://shop.example/product/3?lang=1', $locs);
        $this->assertNotContains('http://shop.example/product/6?lang=1', $locs);
        $this->assertNotContains('http://shop.example/product/7?lang=1', $locs);
        $this->assertNotContains('http://shop.example/product/8?lang=1', $locs);
        $byLoc = array();
        foreach ($links as $link) {
            $byLoc[$link['link']] = $link;
        }
        $this->assertArrayHasKey('http://shop.example/product/4?lang=1', $byLoc);
        $this->assertSame('2020-06-15 13:45:00', $byLoc['http://shop.example/product/4?lang=1']['lastmod']);
        $this->assertSame(
            'http://shop.example/img/p/41-large.jpg',
            $byLoc['http://shop.example/product/4?lang=1']['images'][0]['link']
        );
    }

    public function testProductLinkHonorsUnidentifiedGroup()
    {
        \Group::$featureActive = true;
        \Configuration::set('PS_UNIDENTIFIED_GROUP', 1);
        $this->pdo->exec("INSERT INTO ps_product_shop (id_product, id_shop, active, visibility) VALUES (4,1,1,'both'),(5,1,1,'both')");
        $this->pdo->exec('INSERT INTO ps_category_product (id_category, id_product) VALUES (3,4)');
        $this->pdo->exec('INSERT INTO ps_category_group (id_category, id_group) VALUES (3,1)');
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetProductLink');
        $ids = array();
        foreach ($links as $link) {
            $ids[] = $link['link'];
        }
        $this->assertSame(array('http://shop.example/product/4?lang=1'), $ids);
    }

    public function testCategoryLinkSkipsRootHomeInactiveAndOtherShop()
    {
        $this->pdo->exec("INSERT INTO ps_category (id_category, id_parent, active, id_image, date_upd) VALUES
            (1,0,1,NULL,'2020-06-15 13:45:00'),
            (2,1,1,NULL,'2020-06-15 13:45:00'),
            (3,3,1,NULL,'2020-06-15 13:45:00'),
            (4,0,0,NULL,'2020-06-15 13:45:00'),
            (10,3,1,5,'2020-06-15 13:45:00')");
        $this->pdo->exec('INSERT INTO ps_category_shop (id_category, id_shop) VALUES (3,1),(4,1),(10,1),(10,2)');
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetCategoryLink');
        $locs = array();
        $imageCount = 0;
        foreach ($links as $link) {
            $locs[] = $link['link'];
            if (!empty($link['image'])) {
                ++$imageCount;
            }
        }
        $this->assertContains('http://shop.example/category/3?lang=1', $locs);
        $this->assertContains('http://shop.example/category/10?lang=1', $locs);
        $this->assertNotContains('http://shop.example/category/1?lang=1', $locs);
        $this->assertNotContains('http://shop.example/category/2?lang=1', $locs);
        $this->assertNotContains('http://shop.example/category/4?lang=1', $locs);
        $this->assertSame(1, $imageCount);
    }

    public function testManufacturerAndSupplierObeyListingFlags()
    {
        $this->pdo->exec("INSERT INTO ps_manufacturer (id_manufacturer, active, date_upd) VALUES (1,1,'2020-06-15 13:45:00')");
        $this->pdo->exec('INSERT INTO ps_manufacturer_lang (id_manufacturer, id_lang, link_rewrite) VALUES (1,1,\'m1\')');
        $this->pdo->exec('INSERT INTO ps_manufacturer_shop (id_manufacturer, id_shop) VALUES (1,1)');
        \Configuration::set('PS_DISPLAY_MANUFACTURERS', 0);
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetManufacturerLink');
        $this->assertSame(array(), $links);
        \Configuration::set('PS_DISPLAY_MANUFACTURERS', 1);
        $links = $this->collectViaHome($module, 'exposeGetManufacturerLink');
        $this->assertSame('http://shop.example/manufacturer/1?lang=1', $links[0]['link']);
    }

    public function testCmsLinkRequiresActiveIndexationAndActiveCategory()
    {
        $this->pdo->exec('INSERT INTO ps_cms_category (id_cms_category, active) VALUES (1,1),(2,0)');
        $this->pdo->exec('INSERT INTO ps_cms (id_cms, id_cms_category, active, indexation) VALUES (1,1,1,1),(2,1,1,0),(3,2,1,1)');
        $this->pdo->exec('INSERT INTO ps_cms_lang (id_cms, id_lang, link_rewrite) VALUES (1,1,\'a\'),(2,1,\'b\'),(3,1,\'c\')');
        $this->pdo->exec('INSERT INTO ps_cms_shop (id_cms, id_shop) VALUES (1,1),(2,1),(3,1)');
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetCmsLink');
        $locs = array();
        foreach ($links as $link) {
            $locs[] = $link['link'];
        }
        $this->assertSame(array('http://shop.example/cms/1?lang=1'), $locs);
    }

    public function testModuleHookMergesLinksSetsTypeAndResumes()
    {
        \Hook::setExecResult(array(
            array(array('page' => 'moda', 'link' => 'http://shop.example/mod/a')),
            array(array('page' => 'modb', 'link' => 'http://shop.example/mod/b')),
        ));
        $module = $this->makeModule();
        $links = $this->collectViaHome($module, 'exposeGetModuleLink', array(1));
        $this->assertCount(1, $links);
        $this->assertSame('module', $links[0]['type']);
        $this->assertSame('http://shop.example/mod/b', $links[0]['link']);
        \Hook::setExecResult(array());
        $empty = $this->collectViaHome($module, 'exposeGetModuleLink');
        $this->assertSame(array(), $empty);
    }
}
