<?php

error_reporting(-1);
ini_set('display_errors', '1');
date_default_timezone_set('UTC');

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '1.7.8.0');
}
if (!defined('_DB_PREFIX_')) {
    define('_DB_PREFIX_', 'ps_');
}
if (!defined('_MYSQL_ENGINE_')) {
    define('_MYSQL_ENGINE_', 'InnoDB');
}
if (!defined('__PS_BASE_URI__')) {
    define('__PS_BASE_URI__', '/');
}

$vendor = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($vendor)) {
    fwrite(STDERR, "Run composer install before tests.\n");
    exit(1);
}
require $vendor;

require __DIR__ . '/Support/PrestaShopStubs.php';
require __DIR__ . '/Support/Exceptions.php';
require __DIR__ . '/Support/XmlAssertions.php';
require __DIR__ . '/Support/Schema.php';

$gsitemapActiveRoot = sys_get_temp_dir() . '/gsitemap-ps-root-active';
if (!is_dir($gsitemapActiveRoot)) {
    mkdir($gsitemapActiveRoot, 0777, true);
}
if (!defined('_PS_ROOT_DIR_')) {
    define('_PS_ROOT_DIR_', $gsitemapActiveRoot);
}
if (!defined('_PS_MODULE_DIR_')) {
    define('_PS_MODULE_DIR_', dirname(__DIR__) . '/');
}

require dirname(__DIR__) . '/gsitemap.php';
require dirname(__DIR__) . '/controllers/front/cron.php';
