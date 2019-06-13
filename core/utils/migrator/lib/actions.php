<?php

namespace Migrator\Actions;

use Migrator\DB;
use Migrator\Files;
use Migrator\Helpers;

/**
 * Creates database from the config file
 */
function createDb() {
    echo 'Initializing DB creation...'.PHP_EOL;
    $conn = DB\connectDB(false);
    if (DB\createDB($conn)) {
        echo 'DB created successfully!'.PHP_EOL;
    } else {
        die('Something went wrong!'.PHP_EOL);
    }
}


/**
 * Deletes database from config file
 */
function dropDb() {
    echo 'Initializing DB drop...'.PHP_EOL;
    $conn = DB\connectDB(false);
    if (DB\dropDB($conn)) {
        echo 'DB dropped successfully! Cleaning migrations history...'.PHP_EOL;
        Helpers\clearMigrations(ROOTDIR.'/'.Files\getConfig('history_path'));
        echo 'Done!'.PHP_EOL;
    } else {
        die('Something went wrong!'.PHP_EOL);
    }
}

/**
 * Generates migration based on
 *
 * @param $migrationName
 */
function generateMigration($migrationName) {
    echo 'Initializing migration generating...'.PHP_EOL;
    $date = date('Y-m-d-H-i');
    $migrationsDirPath = ROOTDIR.'/'.Files\getConfig('migrations_path');

    $migrationPath = $migrationsDirPath."/{$date}_{$migrationName}";

    if (!is_dir($migrationsDirPath)) {
        mkdir($migrationsDirPath);
    }

    mkdir($migrationPath);
    if (!touch($migrationPath.'/up.sql') || !touch($migrationPath.'/down.sql')) {
        die('Cannot create migration files!'.PHP_EOL);
    }

    echo 'Migration created!'.PHP_EOL;
}

function migrate() {
    echo 'Initializing migrations...'.PHP_EOL;
    $history_path = Files\getConfig('history_path');
    $migrations_path = Files\getConfig('migrations_path');
    $conn = DB\connectDB();

    foreach (glob(ROOTDIR.'/'.$migrations_path.'/*/up.sql') as $dir) {
        preg_match('/.+\/(.+)\/.+\.sql$/', $dir, $matches);
        $dir_name = $matches[1];

        if (Helpers\readMigrations(ROOTDIR.'/'.$history_path) == false) {
            $query = file_get_contents($dir);

            if (!DB\runQuery($conn, $query)) {
                die('Query could not run!'.PHP_EOL);
            }

            echo "Migration $dir_name complete!".PHP_EOL;
            Helpers\writeMigration(ROOTDIR.'/'.$history_path, $dir_name);
        } else {
            if (in_array($dir_name, Helpers\readMigrations(ROOTDIR.'/'.$history_path))) {
                echo "Migration $dir_name already done.".PHP_EOL;
                continue; // Drop current iteration if migration found in history
            } else {
                $query = file_get_contents($dir);

                if (!DB\runQuery($conn, $query)) {
                    die('Query could not run!'.PHP_EOL);
                }

                echo "Migration $dir_name complete!".PHP_EOL;
                Helpers\writeMigration(ROOTDIR.'/'.$history_path, $dir_name);
            }
        }
    }
}

function rollback($version = 'last') {
    echo 'Initializing rollback...'.PHP_EOL;
    $history_path = Files\getConfig('history_path');
    $migrations_path = Files\getConfig('migrations_path');
    $conn = DB\connectDB();

    if (empty(Helpers\readMigrations(ROOTDIR.'/'.$history_path))) {
        echo "Nothing to rollback!".PHP_EOL;
    } else {
        if ($version === 'last' || $version === false) {
            $migr_history = Helpers\readMigrations(ROOTDIR.'/'.$history_path);
            $last_migr = end($migr_history);
            $query = file_get_contents(ROOTDIR.'/'.$migrations_path."/$last_migr/down.sql");
            DB\runQuery($conn, $query);
            Helpers\deleteLastMigration(ROOTDIR.'/'.$history_path);
            echo "Migration $last_migr was rollbacked!".PHP_EOL;
        } else {
            echo "Specific migration given. Starting rollback...".PHP_EOL;
            $rollback_history = array_reverse(glob(ROOTDIR.'/'.$migrations_path.'/*/down.sql'));
            foreach ($rollback_history as $dir) {
                preg_match('/.+\/(.+)\/.+\.sql$/', $dir, $matches);
                $dir_name = $matches[1];
                if ($dir_name == $version) {
                    // Break the loop
                    echo "Rollbacked to $dir_name successfully!".PHP_EOL;
                    break;
                } else {
                    // Perform rollback
                    $query = file_get_contents($dir);
                    DB\runQuery($conn, $query);
                    echo "Version $dir_name rollbacked!".PHP_EOL;
                    Helpers\deleteLastMigration(ROOTDIR.'/'.$history_path);
                }
            }
        }
    }
}

function refresh() {
    echo 'Starting DB refresh...'.PHP_EOL;
    dropDb();
    createDb();
    migrate();
    echo 'DB refreshed successfully!'.PHP_EOL;
}