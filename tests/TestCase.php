<?php

namespace GsitemapTests;

use GsitemapTests\Support\GsitemapTestable;
use GsitemapTests\Support\Schema;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $psRoot;
    protected $pdo;

    protected function setUp()
    {
        parent::setUp();
        \Configuration::reset();
        \Tools::reset();
        \Hook::reset();
        \Group::reset();
        \Context::reset();
        \Db::resetConnection();

        $this->psRoot = _PS_ROOT_DIR_;
        if (is_dir($this->psRoot)) {
            $this->removeTree($this->psRoot);
        }
        mkdir($this->psRoot, 0777, true);

        $this->recreateSchema();
        $this->seedBaseConfiguration();
    }

    protected function tearDown()
    {
        if ($this->psRoot && is_dir($this->psRoot)) {
            $this->removeTree($this->psRoot);
        }
        \Configuration::reset();
        \Tools::reset();
        \Hook::reset();
        \Group::reset();
        \Context::reset();
        \Db::resetConnection();
        parent::tearDown();
    }

    protected function psRoot()
    {
        return $this->psRoot;
    }

    protected function pathInRoot($relative)
    {
        return rtrim($this->psRoot, '/\\') . DIRECTORY_SEPARATOR . ltrim($relative, '/\\');
    }

    protected function recreateSchema()
    {
        $this->pdo = Schema::apply();
        \Db::setPdo($this->pdo);
    }

    protected function seedBaseConfiguration()
    {
        \Configuration::set('PS_SHOP_DEFAULT', 1);
        \Configuration::set('PS_ROOT_CATEGORY', 1);
        \Configuration::set('PS_HOME_CATEGORY', 2);
        \Configuration::set('PS_DISPLAY_MANUFACTURERS', 1);
        \Configuration::set('PS_DISPLAY_SUPPLIERS', 1);
        \Configuration::set('PS_DISPLAY_BEST_SELLERS', 1);
        \Configuration::set('PS_REWRITING_SETTINGS', 0);
        \Configuration::set('PS_SSL_ENABLED', 0);
        \Configuration::set('GSITEMAP_FREQUENCY', 'weekly');
        \Configuration::set('GSITEMAP_PRIORITY_HOME', 1.0);
        \Configuration::set('GSITEMAP_PRIORITY_PRODUCT', 0.9);
        \Configuration::set('GSITEMAP_PRIORITY_CATEGORY', 0.8);
        \Configuration::set('GSITEMAP_PRIORITY_MANUFACTURER', 0.7);
        \Configuration::set('GSITEMAP_PRIORITY_SUPPLIER', 0.7);
        \Configuration::set('GSITEMAP_PRIORITY_CMS', 0.7);
        \Configuration::set('GSITEMAP_DISABLE_LINKS', '');
        $this->pdo->exec('INSERT INTO `ps_shop` (id_shop) VALUES (1), (2)');
    }

    protected function makeModule()
    {
        $module = new GsitemapTestable();
        $module->context = \Context::getContext();
        $module->context->shop = new \Shop(1);

        return $module;
    }

    protected function langEn()
    {
        return array('id_lang' => 1, 'iso_code' => 'en');
    }

    protected function removeTree($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeTree($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    protected function insertMetaRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->pdo->exec(
                'INSERT INTO `ps_meta` (id_meta, page, configurable) VALUES (' .
                (int) $row['id_meta'] . ', ' . $this->pdo->quote($row['page']) . ', 1)'
            );
        }
    }
}
