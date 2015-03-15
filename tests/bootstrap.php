<?php
define('TESTING_DIRECTORY', __DIR__);
error_reporting(-1);
date_default_timezone_set('UTC');
ini_set('xdebug.show_exception_trace', 0);
$filename = __DIR__ .'/../vendor/autoload.php';
if (!file_exists($filename)) {
    echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~" . PHP_EOL;
    echo " You need to execute `composer install` before running the tests. " . PHP_EOL;
    echo "         Vendors are required for complete test execution.        " . PHP_EOL;
    echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~" . PHP_EOL . PHP_EOL;
    $filename = __DIR__ .'/../autoload.php';
}

/** @var \Composer\Autoload\ClassLoader $classLoader */
$classLoader = require $filename;
$classLoader->addPsr4('RedCode\\Currency\\Tests\\', 'tests');