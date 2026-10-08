<?php

define('_PS_VERSION_', '1.7.6.9');
require dirname(__DIR__) . '/bootstrap.php';

\Configuration::reset();
\Configuration::set('PS_DISPLAY_MANUFACTURERS', 0);
\Configuration::set('PS_DISPLAY_SUPPLIERS', 1);

$module = new GsitemapTests\Support\GsitemapTestable();
if (!$module->exposeIsManufacturerListingEnabled()) {
    fwrite(STDERR, "manufacturer should be enabled via PS_DISPLAY_SUPPLIERS on 1.7.6\n");
    exit(1);
}
echo "OK\n";
