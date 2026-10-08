<?php

use GsitemapTests\Support\ModuleLinkCaptureException;
use GsitemapTests\Support\RedirectException;

class Module
{
    public $name;
    public $tab;
    public $version;
    public $author;
    public $need_instance;
    public $bootstrap;
    public $displayName;
    public $description;
    public $ps_versions_compliancy;
    public $confirmUninstall;
    public $active = true;
    public $upgrade_detail = array();
    public $context;

    public function __construct()
    {
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    public function trans($s, $params = array(), $domain = null)
    {
        return $s;
    }

    public function display($file, $template)
    {
        return 'tpl:' . $template;
    }
}

class Validate
{
    public static function isLoadedObject($obj)
    {
        return is_object($obj) && !empty($obj->id);
    }
}

class Configuration
{
    private static $values = array();

    public static function reset()
    {
        self::$values = array();
    }

    public static function set($key, $value)
    {
        self::$values[$key] = $value;
    }

    public static function get($key)
    {
        return array_key_exists($key, self::$values) ? self::$values[$key] : false;
    }

    public static function updateValue($key, $value)
    {
        self::$values[$key] = $value;

        return true;
    }

    public static function deleteByName($key)
    {
        if (!array_key_exists($key, self::$values)) {
            return false;
        }
        unset(self::$values[$key]);

        return true;
    }

    public static function all()
    {
        return self::$values;
    }
}

class Db
{
    private static $pdo;

    public static function resetConnection()
    {
        self::$pdo = null;
    }

    public static function setPdo(\PDO $pdo)
    {
        self::$pdo = $pdo;
    }

    public static function getInstance()
    {
        return new self();
    }

    private function pdo()
    {
        if (!self::$pdo) {
            list($user, $pass) = \GsitemapTests\Support\DbConfig::credentials();
            self::$pdo = new \PDO(
                \GsitemapTests\Support\DbConfig::dsn(),
                $user,
                $pass,
                array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
            );
        }

        return self::$pdo;
    }

    public function Execute($sql)
    {
        return $this->pdo()->exec($sql) !== false;
    }

    public function ExecuteS($sql)
    {
        $stmt = $this->pdo()->query($sql);
        if ($stmt === false) {
            return false;
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

class Tools
{
    private static $values = array();
    public static $phpCli = false;
    public static $captureModuleLink = false;
    public static $markerDir = null;
    public static $lastModuleLink = null;

    public static function reset()
    {
        self::$values = array();
        self::$phpCli = false;
        self::$captureModuleLink = false;
        self::$markerDir = null;
        self::$lastModuleLink = null;
    }

    public static function setValue($key, $value)
    {
        self::$values[$key] = $value;
    }

    public static function getValue($key)
    {
        return array_key_exists($key, self::$values) ? self::$values[$key] : null;
    }

    public static function getIsset($key)
    {
        return array_key_exists($key, self::$values);
    }

    public static function isSubmit($key)
    {
        return !empty(self::$values[$key]);
    }

    public static function hash($input)
    {
        if ($input === 'gsitemap/cron') {
            return '0123456789abcdef0123456789abcdef';
        }

        return 'wrong-input-hash';
    }

    public static function substr($s, $start, $len = null)
    {
        if ($len === null) {
            return substr($s, $start);
        }

        return substr($s, $start, $len);
    }

    public static function ucfirst($s)
    {
        return ucfirst($s);
    }

    public static function strtoupper($s)
    {
        return strtoupper($s);
    }

    public static function strlen($s)
    {
        return strlen($s);
    }

    public static function isPHPCLI()
    {
        return self::$phpCli;
    }

    public static function getShopDomain($a, $b)
    {
        return 'shop.example';
    }

    public static function redirect($url)
    {
        throw new RedirectException($url);
    }

    public static function redirectAdmin($url)
    {
        throw new RedirectException($url);
    }

    public static function deleteFile($path)
    {
        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }
}

class Link
{
    public function getPageLink($page, $ssl = null, $idLang = null)
    {
        return 'http://shop.example/page/' . $page . '?lang=' . (int) $idLang;
    }

    public function getProductLink($product, $rewrite = null, $cat = null, $ean = null, $idLang = null, $idShop = null, $ipa = 0)
    {
        return 'http://shop.example/product/' . (int) $product->id . '?lang=' . (int) $idLang;
    }

    public function getCategoryLink($category, $rewrite = null, $idLang = null)
    {
        return 'http://shop.example/category/' . (int) $category->id . '?lang=' . (int) $idLang;
    }

    public function getManufacturerLink($manufacturer, $rewrite = null, $idLang = null)
    {
        return 'http://shop.example/manufacturer/' . (int) $manufacturer->id . '?lang=' . (int) $idLang;
    }

    public function getSupplierLink($supplier, $rewrite = null, $idLang = null)
    {
        return 'http://shop.example/supplier/' . (int) $supplier->id . '?lang=' . (int) $idLang;
    }

    public function getCMSLink($cms, $rewrite = null, $ssl = null, $idLang = null)
    {
        return 'http://shop.example/cms/' . (int) $cms->id . '?lang=' . (int) $idLang;
    }

    public function getBaseLink()
    {
        return 'http://shop.example/';
    }

    public function getAdminLink($controller, $ssl = true, $params = array(), $extra = array())
    {
        return 'http://shop.example/admin/' . $controller . '?' . http_build_query($extra);
    }

    public function getModuleLink($module, $controller, array $params = array())
    {
        Tools::$lastModuleLink = array('module' => $module, 'controller' => $controller, 'params' => $params);
        if (Tools::$markerDir) {
            if (!is_dir(Tools::$markerDir)) {
                mkdir(Tools::$markerDir, 0777, true);
            }
            file_put_contents(
                Tools::$markerDir . '/module_link.json',
                json_encode(Tools::$lastModuleLink)
            );
        }
        if (Tools::$captureModuleLink) {
            throw new ModuleLinkCaptureException($module, $controller, $params);
        }
        $q = http_build_query($params);

        return 'http://shop.example/module/' . $module . '/' . $controller . ($q ? '?' . $q : '');
    }

    public function getImageLink($rewrite, $idImage, $type)
    {
        return 'http://shop.example/img/p/' . $idImage . '-large.jpg';
    }

    public function getCatImageLink($rewrite, $idImage, $type)
    {
        return 'http://shop.example/img/c/' . $idImage . '-category.jpg';
    }

    public function getManufacturerImageLink($id, $type)
    {
        return 'http://shop.example/img/m/' . (int) $id . '-medium.jpg';
    }

    public function getSupplierImageLink($id, $type)
    {
        return 'http://shop.example/img/su/' . (int) $id . '-medium.jpg';
    }
}

class Shop
{
    public $id;
    public $virtual_uri = '';
    public $domain = 'shop.example';
    public $physical_uri = '/';

    public function __construct($id = 1)
    {
        $this->id = (int) $id;
    }
}

class Context
{
    public static $instance;
    public $shop;
    public $cookie;
    public $link;
    public $controller;
    public $smarty;

    public function __construct()
    {
        $this->shop = new Shop(1);
        $this->cookie = new stdClass();
        $this->cookie->id_lang = 1;
        $this->link = new Link();
        $this->controller = new stdClass();
        $this->controller->errors = array();
        $this->smarty = new SmartyStub();
    }

    public static function getContext()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function reset()
    {
        self::$instance = new self();
    }
}

class SmartyStub
{
    public $assigned = array();

    public function assign($key, $value = null)
    {
        if (is_array($key)) {
            $this->assigned = array_merge($this->assigned, $key);
        } else {
            $this->assigned[$key] = $value;
        }
    }
}

class Language
{
    public static function getLanguages($active, $idShop)
    {
        return array(
            array('id_lang' => 1, 'iso_code' => 'en'),
        );
    }
}

class Group
{
    public static $featureActive = false;

    public static function isFeatureActive()
    {
        return self::$featureActive;
    }

    public static function reset()
    {
        self::$featureActive = false;
    }
}

class ImageType
{
    public static function getFormattedName($name)
    {
        return $name;
    }
}

class ShopUrl
{
    public static function resetMainDomainCache()
    {
    }
}

class Meta
{
    public static function getMetasByIdLang($idLang)
    {
        $rows = Db::getInstance()->ExecuteS('SELECT id_meta, page, configurable FROM `' . _DB_PREFIX_ . 'meta` ORDER BY id_meta ASC');
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_meta' => (int) $row['id_meta'],
                'page' => $row['page'],
                'title' => 'Title ' . $row['page'],
            );
        }

        return $out;
    }
}

class Product
{
    public $id;
    public $link_rewrite;
    public $category = 'cat';
    public $ean13 = '';
    public $date_upd = '2020-06-15 13:45:00';

    public function __construct($id, $full = false, $idLang = null)
    {
        $this->id = (int) $id;
        $this->link_rewrite = 'p' . (int) $id;
    }

    public function getImages($idLang)
    {
        return array(array('id_image' => (int) $this->id . '1'));
    }
}

class Category
{
    public $id;
    public $link_rewrite;
    public $id_image;
    public $date_upd = '2020-06-15 13:45:00';

    public function __construct($id, $idLang = null)
    {
        $this->id = (int) $id;
        $this->link_rewrite = 'c' . (int) $id;
        $this->id_image = ((int) $id === 10) ? 5 : 0;
    }
}

class Manufacturer
{
    public $id;
    public $link_rewrite;
    public $date_upd = '2020-06-15 13:45:00';

    public function __construct($id, $idLang = null)
    {
        $this->id = (int) $id;
        $this->link_rewrite = 'm' . (int) $id;
    }
}

class Supplier
{
    public $id;
    public $link_rewrite;
    public $date_upd = '2020-06-15 13:45:00';

    public function __construct($id, $idLang = null)
    {
        $this->id = (int) $id;
        $this->link_rewrite = 's' . (int) $id;
    }
}

class CMS
{
    public $id;
    public $link_rewrite;

    public function __construct($id, $idLang = null)
    {
        $this->id = (int) $id;
        $this->link_rewrite = 'cms' . (int) $id;
    }
}

class Hook
{
    public $id;
    public $name;
    public $title;
    public $description;
    public $position;

    private static $hooks = array();

    public function __construct($id = null)
    {
        if ($id) {
            $this->id = (int) $id;
        }
    }
    private static $execResult = array();

    public static function reset()
    {
        self::$hooks = array();
        self::$execResult = array();
    }

    public static function getIdByName($name)
    {
        return isset(self::$hooks[$name]) ? self::$hooks[$name] : 0;
    }

    public static function setHookExists($name, $id)
    {
        self::$hooks[$name] = $id;
    }

    public static function setExecResult($result)
    {
        self::$execResult = $result;
    }

    public function save()
    {
        self::$hooks[$this->name] = 1;
        $this->id = 1;

        return true;
    }

    public static function exec($name, $params = array(), $idShop = null, $array = true)
    {
        return self::$execResult;
    }
}

function pSQL($s)
{
    return addslashes($s);
}

class ModuleFrontController
{
    public $module;
    public $context;

    public function __construct()
    {
        $this->context = Context::getContext();
    }
}
