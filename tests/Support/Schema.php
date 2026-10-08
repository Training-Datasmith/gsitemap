<?php

namespace GsitemapTests\Support;

class Schema
{
    public static function apply()
    {
        list($user, $pass) = DbConfig::credentials();
        $pdo = new \PDO(
            DbConfig::dsn(),
            $user,
            $pass,
            array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
        );

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $tables = array(
            'ps_gsitemap_sitemap',
            'ps_meta',
            'ps_shop',
            'ps_product_shop',
            'ps_category_product',
            'ps_category_group',
            'ps_category',
            'ps_category_shop',
            'ps_manufacturer',
            'ps_manufacturer_lang',
            'ps_manufacturer_shop',
            'ps_supplier',
            'ps_supplier_lang',
            'ps_supplier_shop',
            'ps_cms',
            'ps_cms_lang',
            'ps_cms_shop',
            'ps_cms_category',
        );
        foreach ($tables as $table) {
            $pdo->exec('DROP TABLE IF EXISTS `' . $table . '`');
        }

        $sql = file_get_contents(dirname(__DIR__) . '/schema.sql');
        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement === '' || stripos($statement, 'DROP TABLE') === 0) {
                continue;
            }
            $pdo->exec($statement);
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        return $pdo;
    }
}
