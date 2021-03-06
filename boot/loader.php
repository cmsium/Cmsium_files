<?php
// Bootstrapping the whole application

// Load core libraries
use Config\ConfigManager;
use Plumber\Plumber;

require_once dirname(__DIR__).'/boot/defaults.php';
require_once dirname(__DIR__).'/core/autoload.php';

//load exceptions
foreach (glob(ROOTDIR."/app/exceptions/*.php") as $class){
    require_once $class;
}

$router = new \Router\Router;

// Build and load application instance
$application = \Webgear\Swoole\Application::getInstance($router);

// Load app routes
include ROOTDIR.'/app/routes.php';

$app_config = \Config\ConfigManager::module('app');
$env = $app_config->get('env');

// Register middleware callbacks
$plumber = \Plumber\Plumber::getInstance();
$pre = $plumber->buildPipeline('webgear.pre');
$post = $plumber->buildPipeline('webgear.post');
$api = $plumber->buildPipeline('routes.api');
include ROOTDIR.'/app/middleware.php';


// Create swoole table for links
$table = new swoole_table(100000);
$table->column('hash', swoole_table::TYPE_STRING, 256);
$table->column('file', swoole_table::TYPE_STRING, 32);
$table->column('temp', swoole_table::TYPE_INT, 1);
$table->column('expire', swoole_table::TYPE_STRING, 32);
$table->column('type', swoole_table::TYPE_STRING, 6);
$table->create();
$application->links = $table;
//TODO db based structure?

// Create swoole table for files
$files_table = new swoole_table(1000000);
$files_table->column('file_id', swoole_table::TYPE_STRING, 32);
$files_table->column('path', swoole_table::TYPE_STRING, 255);
$files_table->column('name', swoole_table::TYPE_STRING, 255);
$files_table->column('is_delete', swoole_table::TYPE_INT, 1);
$files_table->create();
$application->files = $files_table;

//Host info
$application->host = $app_config->get('host_url');

//Error handler
$application->error_handler = new \Errors\AppErrorHandler(
    \Presenter\PageBuilder::getInstance(),
    "error"
);

// TODO: Rework app environments handling
if ($env == 'test') {
    foreach (TEST_HELPERS as $helperFile) {
        include ROOTDIR.'/helpers/'.$helperFile;
    }
    require_once dirname(__DIR__).'/boot/files.defaults.test.php';
    require_once ROOTDIR.'/tests/AppTestCase.php';
} else {
    require_once dirname(__DIR__).'/boot/files.defaults.php';
    // Load helper functions. Add file to helpers array to load it.
    foreach (HELPERS as $helperFile) {
        include ROOTDIR.'/helpers/'.$helperFile;
    }

    //Create Queue Exchange client
    $config = Config\ConfigManager::module('queue');
    $ex_host = $config->get('host');
    $ex_port = $config->get('port');
    $client = new \Queue\Producers\Producer($ex_host, $ex_port);
    $application->queue_client = $client;

    //Create Controller client
    $url = $app_config->get('controller_url');
    $port = $app_config->get('controller_port');
    $timeout = $app_config->get('controller_timeout');
    $client = new \App\ControllerClient($url, $port, $timeout);
    $application->controller_client = $client;

    // Start file delete coroutine
    $application->registerStartupCallback(function () use ($ex_host, $ex_port) {
        $consumer = new \Queue\Consumers\Consumer($ex_host, $ex_port);
        $consumer->subscribe('files.delete');
        $consumer->on('files.delete', function ($data) {
            unlink($data['path']);
        }, 100);
    });

    // Warm up links cache
    $application->registerStartupCallback(function () use ($application) {
        go(function () use ($application) {
            $mysql = new \DB\SwooleMysqlConnection();
            $links = $mysql->select("SELECT * FROM links;");
            if ($links) {
                foreach ($links as $data) {
                    $link = new \App\Link($data, $application->links);
                    $link->swooleSave();
                }
            }
            unset($links);
        });
    });

    // Warm up files cache
    $application->registerStartupCallback(function () use ($application) {
        go(function () use ($application) {
            $mysql = new \DB\SwooleMysqlConnection();
            $files = $mysql->select("SELECT * FROM files;");
            if ($files) {
                foreach ($files as $data) {
                    $file = new \App\AppFile($application->files);
                    $file->createFromData($data);
                    $file->swooleSave();
                }
            }
            unset($files);
        });
    });

    //Clean expired links coro
    $manager = new \App\LinksManager($application->links);

    $application->registerStartupCallback(function () use ($manager) {
        go(function () use ($manager){
            \swoole_timer_tick(CLEAN_EXPIRED_LINKS_TIME, [$manager, 'cleanExpiredLinks']);
        });
    });

    //Clean links associated with deleted files coro
    $application->registerStartupCallback(function () use ($manager) {
        go(function () use ($manager) {
            \swoole_timer_tick(CLEAN_DELETED_FILES_LINKS_TIME, [$manager, 'cleanDeletedFilesLinks']);
        });
    });
}