<?php

use Yeepliva\Autoloader;
use Yeepliva\Core\App;

// Import file
require_once dirname(__DIR__) . '/app/Autoloader.php';

// Autoloader
Autoloader::register();

// Application
$app = new App();
$app->run();