<?php
/**
 * This file is executable
 * Use this script to fill your DB with migrations from the 'migrations' directory
 */
require_once dirname(__DIR__).'/../config/defaults.php';
require_once dirname(__DIR__).'/../config/requires_templates/requires.product.php';
require_once ROOTDIR."/lib/database/Migration.php";
require_once ROOTDIR."/lib/database/MigrationHistoryHandler.php";
$history_path = Config::get('history_path');
DBConnection::dropDB();
MigrationHistoryHandler::clear(ROOTDIR.$history_path);