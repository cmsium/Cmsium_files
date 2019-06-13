<?php

namespace Presenter;

use Config\ConfigManager;

/**
 * Class PageBuilder (Singleton)
 *
 * @package Presenter
 */
class PageBuilder {

    private $config;
    public static $instance;

    public static function getInstance() : self {
        if (static::$instance != null) {
            return static::$instance;
        }

        static::$instance = new static;
        return static::$instance;
    }

    public function __construct() {
        $this->config = ConfigManager::module('presenter');
    }

    public function build($templateName) : Page {
        $templateDir = ROOTDIR."/{$this->config->get('templates_path')}";
        $templateLocation = "$templateDir/$templateName.html.php";
        $template = new Template($templateDir, $templateLocation);
        $page = new Page($template);
        return $page;
    }

}