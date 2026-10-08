<?php

namespace GsitemapTests\Support;

class RedirectException extends \Exception
{
    public $url;

    public function __construct($url)
    {
        parent::__construct('Redirect: ' . $url);
        $this->url = $url;
    }
}

class ModuleLinkCaptureException extends \Exception
{
    public $module;
    public $controller;
    public $params;

    public function __construct($module, $controller, array $params)
    {
        $this->module = $module;
        $this->controller = $controller;
        $this->params = $params;
        parent::__construct('Module link capture');
    }
}
