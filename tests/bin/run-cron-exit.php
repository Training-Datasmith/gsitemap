<?php

require dirname(__DIR__) . '/bootstrap.php';

$host = getenv('GSITEMAP_DB_HOST') ? getenv('GSITEMAP_DB_HOST') : '127.0.0.1';
$pdo = GsitemapTests\Support\Schema::apply();
Db::setPdo($pdo);
if (!is_dir(_PS_ROOT_DIR_)) {
    mkdir(_PS_ROOT_DIR_, 0777, true);
}
Configuration::set('PS_ROOT_CATEGORY', 1);
Configuration::set('PS_HOME_CATEGORY', 2);
Configuration::set('GSITEMAP_FREQUENCY', 'weekly');

$module = new GsitemapTests\Support\GsitemapTestable();
$module->context = Context::getContext();
$module->context->shop = new Shop(1);
$module->cron = true;
$module->createSitemap();
echo "NO_EXIT\n";
exit(1);
