<?php

namespace Tests;

use Faker\Factory;
use Testgear\TestCase;

abstract class AppTestCase extends TestCase {

    public $faker;

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->setApplication(\Webgear\Swoole\Application::getInstance());
        $this->faker = Factory::create();
    }

}