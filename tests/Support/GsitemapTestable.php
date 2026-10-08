<?php

namespace GsitemapTests\Support;

class GsitemapTestable extends \Gsitemap
{
    public function exposeNormalizeDirectory($directory)
    {
        return $this->normalizeDirectory($directory);
    }

    public function exposeRemoveControlCharacters($text)
    {
        return $this->removeControlCharacters($text);
    }

    public function exposeGetPriorityPage($page)
    {
        return $this->getPriorityPage($page);
    }

    public function exposeGetDisabledMetas()
    {
        return $this->getDisabledMetas();
    }

    public function exposeGetMetasForConfiguration()
    {
        return $this->getMetasForConfiguration();
    }

    public function exposeIsManufacturerListingEnabled()
    {
        return $this->isManufacturerListingEnabled();
    }

    public function exposeIsSupplierListingEnabled()
    {
        return $this->isSupplierListingEnabled();
    }

    public function exposeIsBestSellersListingEnabled()
    {
        return $this->isBestSellersListingEnabled();
    }

    public function exposeAddSitemapNode($fd, $loc, $priority, $change_freq, $last_mod = null)
    {
        return $this->addSitemapNode($fd, $loc, $priority, $change_freq, $last_mod);
    }

    public function exposeAddSitemapNodeImage($fd, $link)
    {
        return $this->addSitemapNodeImage($fd, $link);
    }

    public function exposeRecursiveSitemapCreator($link_sitemap, $lang, &$index)
    {
        return $this->recursiveSitemapCreator($link_sitemap, $lang, $index);
    }

    public function exposeCreateIndexSitemap()
    {
        return $this->createIndexSitemap();
    }

    public function exposeGetHomeLink(&$link_sitemap, $lang, &$index, &$i)
    {
        return $this->getHomeLink($link_sitemap, $lang, $index, $i);
    }

    public function exposeGetMetaLink(&$link_sitemap, $lang, &$index, &$i, $id_meta = 0)
    {
        return $this->getMetaLink($link_sitemap, $lang, $index, $i, $id_meta);
    }

    public function exposeGetProductLink(&$link_sitemap, $lang, &$index, &$i, $id_product = 0)
    {
        return $this->getProductLink($link_sitemap, $lang, $index, $i, $id_product);
    }

    public function exposeGetCategoryLink(&$link_sitemap, $lang, &$index, &$i, $id_category = 0)
    {
        return $this->getCategoryLink($link_sitemap, $lang, $index, $i, $id_category);
    }

    public function exposeGetManufacturerLink(&$link_sitemap, $lang, &$index, &$i, $id_manufacturer = 0)
    {
        return $this->getManufacturerLink($link_sitemap, $lang, $index, $i, $id_manufacturer);
    }

    public function exposeGetSupplierLink(&$link_sitemap, $lang, &$index, &$i, $id_supplier = 0)
    {
        return $this->getSupplierLink($link_sitemap, $lang, $index, $i, $id_supplier);
    }

    public function exposeGetCmsLink(&$link_sitemap, $lang, &$index, &$i, $id_cms = 0)
    {
        return $this->getCmsLink($link_sitemap, $lang, $index, $i, $id_cms);
    }

    public function exposeGetModuleLink(&$link_sitemap, $lang, &$index, &$i, $num_link = 0)
    {
        return $this->getModuleLink($link_sitemap, $lang, $index, $i, $num_link);
    }
}
