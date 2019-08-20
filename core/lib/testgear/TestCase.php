<?php

namespace Testgear;

use Testgear\DB\RefreshTables;

abstract class TestCase extends \PHPUnit\Framework\TestCase {

    use CanRequestToApp;

    protected function setUp(): void {
        parent::setUp();

        if (in_array(RefreshTables::class, class_uses(static::class))) {
            $this->refreshDB();
        }
    }

}