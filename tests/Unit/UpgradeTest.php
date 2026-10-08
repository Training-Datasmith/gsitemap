<?php

namespace GsitemapTests\Unit;

use GsitemapTests\TestCase;

class UpgradeTest extends TestCase
{
    public function testUpgrade500DeletesLegacyCronFile()
    {
        $legacy = _PS_MODULE_DIR_ . 'gsitemap/gsitemap-cron.php';
        $legacyDir = dirname($legacy);
        if (!is_dir($legacyDir)) {
            mkdir($legacyDir, 0777, true);
        }
        file_put_contents($legacy, '<?php');
        $module = $this->makeModule();
        require_once dirname(__DIR__) . '/../upgrade/upgrade-5.0.0.php';
        $this->assertTrue(upgrade_module_5_0_0($module));
        $this->assertFileNotExists($legacy);
    }

    public function testUpgrade440SetsManufacturerPriority()
    {
        \Configuration::deleteByName('GSITEMAP_PRIORITY_MANUFACTURER');
        require_once dirname(__DIR__) . '/../upgrade/upgrade-4.4.0.php';
        $module = $this->makeModule();
        $this->assertTrue(upgrade_module_4_4_0($module));
        $this->assertSame(0.7, \Configuration::get('GSITEMAP_PRIORITY_MANUFACTURER'));
    }

    public function testUpgrade431DeletesImageCheckKey()
    {
        \Configuration::set('GSITEMAP_CHECK_IMAGE_FILE', 1);
        require_once dirname(__DIR__) . '/../upgrade/upgrade-4.3.1.php';
        $module = $this->makeModule();
        $this->assertTrue(upgrade_module_4_3_1($module));
        $this->assertFalse(\Configuration::get('GSITEMAP_CHECK_IMAGE_FILE'));
    }

    public function testUpgrade220RebuildsTableWhenActive()
    {
        \Configuration::deleteByName('GSITEMAP_PRIORITY_HOME');
        $this->pdo->exec('DROP TABLE IF EXISTS `ps_gsitemap_sitemap`');
        require_once dirname(__DIR__) . '/../upgrade/install-2.2.0.php';
        $module = $this->makeModule();
        $module->active = true;
        $this->assertTrue(upgrade_module_2_2_0($module));
        $this->assertSame(1.0, \Configuration::get('GSITEMAP_PRIORITY_HOME'));
        $rows = $this->pdo->query("SHOW TABLES LIKE 'ps_gsitemap_sitemap'")->fetchAll();
        $this->assertNotEmpty($rows);
    }

    public function testUpgrade220RefusesWhenInactive()
    {
        require_once dirname(__DIR__) . '/../upgrade/install-2.2.0.php';
        $module = $this->makeModule();
        $module->active = false;
        $this->assertFalse(upgrade_module_2_2_0($module, false));
        $this->assertNotEmpty($module->upgrade_detail['2.2']);
    }
}
