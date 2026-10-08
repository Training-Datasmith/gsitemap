DROP TABLE IF EXISTS `ps_gsitemap_sitemap`;
DROP TABLE IF EXISTS `ps_meta`;
DROP TABLE IF EXISTS `ps_shop`;
DROP TABLE IF EXISTS `ps_product_shop`;
DROP TABLE IF EXISTS `ps_category_product`;
DROP TABLE IF EXISTS `ps_category_group`;
DROP TABLE IF EXISTS `ps_category`;
DROP TABLE IF EXISTS `ps_category_shop`;
DROP TABLE IF EXISTS `ps_manufacturer`;
DROP TABLE IF EXISTS `ps_manufacturer_lang`;
DROP TABLE IF EXISTS `ps_manufacturer_shop`;
DROP TABLE IF EXISTS `ps_supplier`;
DROP TABLE IF EXISTS `ps_supplier_lang`;
DROP TABLE IF EXISTS `ps_supplier_shop`;
DROP TABLE IF EXISTS `ps_cms`;
DROP TABLE IF EXISTS `ps_cms_lang`;
DROP TABLE IF EXISTS `ps_cms_shop`;
DROP TABLE IF EXISTS `ps_cms_category`;

CREATE TABLE `ps_gsitemap_sitemap` (
  `link` varchar(255) DEFAULT NULL,
  `id_shop` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_meta` (
  `id_meta` int(11) NOT NULL,
  `page` varchar(64) NOT NULL,
  `configurable` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_meta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_shop` (
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_product_shop` (
  `id_product` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `visibility` varchar(16) NOT NULL DEFAULT 'both',
  PRIMARY KEY (`id_product`, `id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_category_product` (
  `id_category` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  PRIMARY KEY (`id_category`, `id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_category_group` (
  `id_category` int(11) NOT NULL,
  `id_group` int(11) NOT NULL,
  PRIMARY KEY (`id_category`, `id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_category` (
  `id_category` int(11) NOT NULL,
  `id_parent` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `id_image` int(11) DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_category_shop` (
  `id_category` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_category`, `id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_manufacturer` (
  `id_manufacturer` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_manufacturer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_manufacturer_lang` (
  `id_manufacturer` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `link_rewrite` varchar(128) NOT NULL,
  PRIMARY KEY (`id_manufacturer`, `id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_manufacturer_shop` (
  `id_manufacturer` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_manufacturer`, `id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_supplier` (
  `id_supplier` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_supplier_lang` (
  `id_supplier` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `link_rewrite` varchar(128) NOT NULL,
  PRIMARY KEY (`id_supplier`, `id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_supplier_shop` (
  `id_supplier` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_supplier`, `id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_cms` (
  `id_cms` int(11) NOT NULL,
  `id_cms_category` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `indexation` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_cms`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_cms_lang` (
  `id_cms` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `link_rewrite` varchar(128) NOT NULL,
  PRIMARY KEY (`id_cms`, `id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_cms_shop` (
  `id_cms` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_cms`, `id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ps_cms_category` (
  `id_cms_category` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_cms_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
