<?php
// Bootstrapping the whole application

// Load core libraries
require_once dirname(__DIR__).'/boot/defaults.php';
require_once ROOTDIR.'/core/autoload.php';

//load exceptions
foreach (glob(ROOTDIR."/app/exceptions/*.php") as $class){
    require_once $class;
}

// Load app routes
$router = new \Router\Router;
include ROOTDIR.'/app/routes.php';

// Build and load application instance
$application = \Webgear\Swoole\Application::getInstance($router);

// Prepare mysql connection data
$config = Config\ConfigManager::module('db');
$conn = [
    "host" => $config->get('servername'),
    "port" => (int)$config->get('port'),
    "database" => $config->get('dbname'),
    "user" => $config->get('username'),
    "password" => $config->get('password')
];
$application->mysql = $conn;


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

//Create Queue Exchange client
$ini = parse_ini_file(ROOTDIR."/core/lib/queue/config/exchange.ini");
$host = $ini['host'];
$port = $ini['port'];
$client = new \Queue\Producers\Producer($host, $port);
$application->queue_client = $client;

//Create Controller client
$config = \Config\ConfigManager::module('app');
$url = $config->get('controller_url');
$port = $config->get('controller_port');
$timeout = $config->get('controller_timeout');
$client = new \App\ControllerClient($url, $port, $timeout);
$application->controller_client = $client;


// Register middleware callbacks
$plumber = \Plumber\Plumber::getInstance();
$pre = $plumber->buildPipeline('webgear.pre');
$post = $plumber->buildPipeline('webgear.post');
include ROOTDIR.'/app/middleware.php';

// Load helper functions. Add file to helpers array to load it.
foreach (HELPERS as $helperFile) {
    include ROOTDIR.'/helpers/'.$helperFile;
}

// Start file delete coroutine
$application->registerStartupCallback(function(){
    $consumer = new \Queue\Consumers\Consumer("127.0.0.1", 9503);
    $consumer->subscribe('files.delete');
    $consumer->on('files.delete', function ($data) {
        unlink($data['path']);
    }, 100);
});

// Warm up links cache
$application->registerStartupCallback(function () use ($application) {
    go(function () use ($application) {
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($application->mysql);
        $links = $mysql->query("SELECT * FROM links;");
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
        $mysql = new \Swoole\Coroutine\MySQL();
        $mysql->connect($application->mysql);
        $files = $mysql->query("SELECT * FROM files;");
        if ($files) {
            foreach ($files as $data) {
                $file = new \App\File($application->files);
                $file->createFromData($data);
                $file->swooleSave();
            }
        }
        unset($files);
    });
});

