<?php

namespace GsitemapTests\Support;

trait XmlAssertions
{
    protected function loadUrlset($path)
    {
        $this->assertFileExists($path);
        $dom = new \DOMDocument();
        $this->assertTrue($dom->load($path), 'urlset XML must parse');
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xpath->registerNamespace('image', 'http://www.google.com/schemas/sitemap-image/1.1');

        return array($dom, $xpath);
    }

    protected function loadSitemapIndex($path)
    {
        $this->assertFileExists($path);
        $dom = new \DOMDocument();
        $this->assertTrue($dom->load($path), 'sitemapindex XML must parse');
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        return array($dom, $xpath);
    }

    protected function firstUrlLoc($xpath)
    {
        $nodes = $xpath->query('//sm:url/sm:loc');
        $this->assertGreaterThan(0, $nodes->length);

        return $nodes->item(0)->textContent;
    }

    protected function urlLocs($xpath)
    {
        $nodes = $xpath->query('//sm:url/sm:loc');
        $out = array();
        for ($i = 0; $i < $nodes->length; ++$i) {
            $out[] = $nodes->item($i)->textContent;
        }

        return $out;
    }

    protected function indexLocs($xpath)
    {
        $nodes = $xpath->query('//sm:sitemap/sm:loc');
        $out = array();
        for ($i = 0; $i < $nodes->length; ++$i) {
            $out[] = $nodes->item($i)->textContent;
        }

        return $out;
    }
}
