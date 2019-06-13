<?php

namespace Presenter;

class Template {

    public $templatesPath;
    public $location;

    public function __construct($templatesPath, $location) {
        $this->location = $location;
        $this->templatesPath = $templatesPath;
    }

    // TODO: Assets
    public function process($variables) {
        extract(['templatesPath' => $this->templatesPath]);
        extract($variables);
        ob_start();
        include $this->location;
        return ob_get_clean();
    }

}