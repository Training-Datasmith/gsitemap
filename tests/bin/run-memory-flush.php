<?php

ini_set('memory_limit', '-1');
require dirname(__DIR__) . '/bootstrap.php';

$pdo = GsitemapTests\Support\Schema::apply();
Db::setPdo($pdo);

$root = _PS_ROOT_DIR_;
if (!is_dir($root)) {
    mkdir($root, 0777, true);
} else {
    foreach (glob($root . '/*') as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
Configuration::set('GSITEMAP_FREQUENCY', 'weekly');
Configuration::set('PS_ROOT_CATEGORY', 1);
Configuration::set('PS_HOME_CATEGORY', 2);

$module = new GsitemapTests\Support\GsitemapTestable();
$module->context = Context::getContext();
$module->context->shop = new Shop(1);
$module->cron = false;

$block = str_repeat('x', 1024 * 1024);
$data = array();
while (memory_get_usage() < 100000000) {
    $data[] = $block;
}

$linkSitemap = array(array('type' => 'home', 'page' => 'home', 'link' => 'http://shop.example/old', 'image' => false));
$index = 0;
$i = 0;
$newLink = array('type' => 'product', 'page' => 'product', 'link' => 'http://shop.example/new', 'image' => false);
$chunk = rtrim($root, '/\\') . '/1_en_0_sitemap.xml';
try {
    $module->addLinkToSitemap($linkSitemap, $newLink, 'en', $index, $i, 42);
} catch (GsitemapTests\Support\RedirectException $e) {
    if (!file_exists($chunk)) {
        fwrite(STDERR, "chunk not written\n");
        exit(1);
    }
    if (strpos($e->url, 'id=42') === false) {
        fwrite(STDERR, "missing resume id\n");
        exit(1);
    }
    echo "OK\n";
    exit(0);
}
fwrite(STDERR, "expected redirect\n");
exit(1);
