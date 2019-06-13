<?php


namespace Router;

trait Routable{
    public $request;

    public function __construct($request){
        $this->request = $request;
    }
}