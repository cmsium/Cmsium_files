<?php

namespace Testgear\DB;

use DB\Migration\MigrationFile;
use DB\Migration\Migrator;
use Mockery;

trait RefreshTables {

    /**
     * @var Migrator
     */
    public $migrator;

    protected function refreshDB() {
        if (!isset($this->migrator)) {
            // Prepare mocked driver
            $driver = Mockery::mock('FakeMigrationFile, DB\Interfaces\MigrationStorageDriver');
            $driver->shouldReceive('read')->andReturn([]);
            $driver->shouldReceive('write')->andReturn(true);
            $driver->shouldReceive('clear')->andReturn(true);
            $driver->shouldReceive('deleteLast')->andReturn(true);

            $this->migrator = new Migrator($driver);
        }

        $this->migrator->refresh();
    }

    public function assertDatabaseHas($table, array $params) {
        $result = $this->queryDatabase($table, $params);
        $this->assertNotCount(0, $result, 'Given data not present in the database.');
    }

    public function assertDatabaseMissing($table, $params) {
        $result = $this->queryDatabase($table, $params);
        $this->assertCount(0, $result, 'Given data is present in the database.');
    }

    protected function queryDatabase($table, array $params) {
        $query = "SELECT * FROM $table WHERE ";
        // Make array of prepared statement params like ['name = ?', 'id = ?'], then glue it with AND
        $query .= implode(' AND ', array_map(
            function($key) {
                return "$key = ?";
            },
            array_keys($params)
        ));

        return $this->migrator->connection->select($query, array_values($params));
    }

}