<?php

require dirname(__DIR__) . '/bootstrap.php';

$mode = $argv[1];
$markerDir = $argv[2];
file_put_contents($markerDir . '/started', '1');

$host = getenv('GSITEMAP_DB_HOST') ? getenv('GSITEMAP_DB_HOST') : '127.0.0.1';
$pdo = GsitemapTests\Support\Schema::apply();
Db::setPdo($pdo);
$pdo->exec('INSERT INTO ps_shop (id_shop) VALUES (1),(2)');
Configuration::set('PS_SHOP_DEFAULT', 1);
Configuration::set('PS_ROOT_CATEGORY', 1);
Configuration::set('PS_HOME_CATEGORY', 2);
Configuration::set('GSITEMAP_FREQUENCY', 'weekly');

class GsitemapCronSpy extends GsitemapTests\Support\GsitemapTestable
{
    public $markerDir;

    public function emptySitemap($id_shop = 0)
    {
        file_put_contents($this->markerDir . '/emptied', (string) $id_shop);
        return parent::emptySitemap($id_shop);
    }

    public function createSitemap($id_shop = 0)
    {
        file_put_contents($this->markerDir . '/generated', (string) $id_shop);
        file_put_contents($this->markerDir . '/shop.json', json_encode(array('shop' => (int) $id_shop)));
        exit(0);
    }
}

$module = new GsitemapCronSpy();
$module->markerDir = $markerDir;
$controller = new GsitemapCronModuleFrontController();
$controller->module = $module;
$controller->context = Context::getContext();

if ($mode === 'bad') {
    Tools::$phpCli = false;
    Tools::setValue('token', '0000000000');
    Tools::setValue('id_shop', 1);
} elseif ($mode === 'cli') {
    Tools::$phpCli = true;
} elseif ($mode === 'shop2') {
    Tools::$phpCli = false;
    Tools::setValue('token', '0123456789');
    Tools::setValue('id_shop', '2');
} elseif ($mode === 'shop99') {
    Tools::$phpCli = false;
    Tools::setValue('token', '0123456789');
    Tools::setValue('id_shop', '99');
} elseif ($mode === 'continue') {
    Tools::$phpCli = false;
    Tools::setValue('token', '0123456789');
    Tools::setValue('continue', '1');
    Tools::setValue('id_shop', 1);
}

$controller->postProcess();
echo "FELL_THROUGH\n";
exit(1);
