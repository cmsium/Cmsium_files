<?php

namespace DB\Migration;

use Config\ConfigManager;
use DB\Exceptions\MigrationException;
use DB\Interfaces\MigrationStorageDriver;
use DB\MysqlConnection;

class Migrator {

    /**
     * @var MysqlConnection
     */
    public $connection;
    public $isDbnameSet = false;
    protected $config;
    protected $migrations;

    public function __construct(MigrationStorageDriver $migrationFileDriver) {
        $this->setDbConnection(false);
        $this->config = ConfigManager::module('db');
        $this->migrations = $migrationFileDriver;
    }

    public function createDb() {
        $this->checkDbNameMissing();

        $query = "CREATE SCHEMA IF NOT EXISTS `{$this->config->get('dbname')}` DEFAULT CHARACTER SET utf8;";
        $this->connection->statement($query);
        return $this;
    }

    public function dropDb() {
        $this->checkDbNameMissing();

        $query = "DROP SCHEMA IF EXISTS `{$this->config->get('dbname')}`;";
        if ($this->connection->statement($query)) {
            $this->migrations->clear();
        }
        return $this;
    }

    public function generateMigration($migrationName) {
        $date = date('Y-m-d-H-i');
        $migrationsDirPath = ROOTDIR.'/'.$this->config->get('migrations_path');

        $migrationPath = $migrationsDirPath."/{$date}_{$migrationName}";

        if (!is_dir($migrationsDirPath)) {
            mkdir($migrationsDirPath);
        }

        mkdir($migrationPath);
        if (!touch($migrationPath.'/up.sql') || !touch($migrationPath.'/down.sql')) {
            throw new MigrationException('Cannot create migration files!');
        }
    }

    public function migrate() {
        $this->checkDbNamePresent();

        $migrations_path = $this->config->get('migrations_path');

        foreach (glob(ROOTDIR.$migrations_path.'/*/up.sql') as $dir) {
            preg_match('/.+\/(.+)\/.+\.sql$/', $dir, $matches);
            $dir_name = $matches[1];

            if (empty($this->migrations->read()) || empty($this->migrations->read()[0])) {
                $query = file_get_contents($dir);

                $this->connection->statement($query);

                $this->migrations->write($dir_name);
            } else {
                if (in_array($dir_name, $this->migrations->read())) {
                    continue; // Drop current iteration if migration found in history
                } else {
                    $query = file_get_contents($dir);

                    $this->connection->statement($query);

                    $this->migrations->write($dir_name);
                }
            }
        }

        return $this;
    }

    public function rollback($version = 'last') {
        $this->checkDbNamePresent();

        $migrations_path = $this->config->get('migrations_path');

        if (!empty($this->migrations->read())) {
            if ($version === 'last' || $version === false) {
                $migr_history = $this->migrations->read();
                $last_migr = end($migr_history);
                $query = file_get_contents(ROOTDIR.$migrations_path."/$last_migr/down.sql");
                $this->connection->statement($query);
                $this->migrations->deleteLast();

                return $this;
            } else {
                // Specific migration given.
                $rollback_history = array_reverse(glob(ROOTDIR.$migrations_path.'/*/down.sql'));
                foreach ($rollback_history as $dir) {
                    preg_match('/.+\/(.+)\/.+\.sql$/', $dir, $matches);
                    $dir_name = $matches[1];
                    if ($dir_name == $version) {
                        // Break the loop
                        break;
                    } else {
                        // Perform rollback
                        $query = file_get_contents($dir);
                        $this->connection->statement($query);
                        $this->migrations->deleteLast();
                    }
                }

                return $this;
            }
        }
    }

    public function refresh() {
        $this->dropDb();
        $this->createDb();
        $this->migrate();

        return $this;
    }

    private function checkDbNamePresent() {
        if (!$this->isDbnameSet) {
            $this->setDbConnection();
        }
    }

    private function checkDbNameMissing() {
        if ($this->isDbnameSet) {
            $this->setDbConnection(false);
        }
    }

    private function setDbConnection($useDb = true) {
        if (isset($this->connection)) {
            $this->connection = null;
        }

        $this->connection = new MysqlConnection($useDb);
        $this->isDbnameSet = $useDb;
    }

    public function __destruct() {
        $this->connection = null;
    }

}