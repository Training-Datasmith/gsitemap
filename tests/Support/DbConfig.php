<?php

namespace GsitemapTests\Support;

class DbConfig
{
    public static function dsn()
    {
        $host = getenv('GSITEMAP_DB_HOST') ? getenv('GSITEMAP_DB_HOST') : '127.0.0.1';
        $port = getenv('GSITEMAP_DB_PORT') ? getenv('GSITEMAP_DB_PORT') : '3306';
        $name = getenv('GSITEMAP_DB_NAME') ? getenv('GSITEMAP_DB_NAME') : 'gsitemap';

        return 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8';
    }

    public static function credentials()
    {
        return array(
            getenv('GSITEMAP_DB_USER') ? getenv('GSITEMAP_DB_USER') : 'gsitemap',
            getenv('GSITEMAP_DB_PASSWORD') ? getenv('GSITEMAP_DB_PASSWORD') : 'gsitemap',
        );
    }
}
