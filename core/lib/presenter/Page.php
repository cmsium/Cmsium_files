<?php

namespace Presenter;

class Page {

    public $template;
    public $parameters = [];
    public $output;

    public function __construct(Template $template) {
        $this->template = $template;
    }

    /**
     * Takes parameters to show in template as array
     * For Example: ['varName' => 'varValue', ...]
     *
     * @param array $parameters
     * @return $this
     */
    public function with(array $parameters) {
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }

    public function render() {
        $this->output = $this->template->process($this->parameters);
        return $this->output;
    }

}