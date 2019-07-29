<?php

namespace DB\Interfaces;

interface MigrationStorageDriver {

    public function read() : array;

    public function write($version);

    public function clear();

    public function deleteLast();

}