<?php


namespace  Routes;

trait Routable{
    public $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
}