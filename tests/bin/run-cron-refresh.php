<?php

require dirname(__DIR__) . '/bootstrap.php';

$markerDir = $argv[1];
if (!is_dir($markerDir)) {
    mkdir($markerDir, 0777, true);
}
$host = getenv('GSITEMAP_DB_HOST') ? getenv('GSITEMAP_DB_HOST') : '127.0.0.1';
$name = getenv('GSITEMAP_DB_NAME') ? getenv('GSITEMAP_DB_NAME') : 'gsitemap';
$user = getenv('GSITEMAP_DB_USER') ? getenv('GSITEMAP_DB_USER') : 'gsitemap';
$pass = getenv('GSITEMAP_DB_PASSWORD') ? getenv('GSITEMAP_DB_PASSWORD') : 'gsitemap';
$pdo = GsitemapTests\Support\Schema::apply();
Db::setPdo($pdo);
Configuration::set('GSITEMAP_FREQUENCY', 'weekly');

Tools::$markerDir = $markerDir;
$module = new GsitemapTests\Support\GsitemapTestable();
$module->context = Context::getContext();
$module->context->shop = new Shop(1);
$module->cron = true;

$linkSitemap = array(
    array(
        'type' => 'home',
        'page' => 'home',
        'link' => 'http://shop.example/old',
        'image' => false,
    ),
);
$index = 19;
$i = 25001;
$newLink = array('type' => 'product', 'page' => 'product', 'link' => 'http://shop.example/new', 'image' => false);
$module->addLinkToSitemap($linkSitemap, $newLink, 'en', $index, $i, 77);
echo "UNEXPECTED\n";
exit(1);
