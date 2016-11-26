<?php

/*
 * Import composer autoload
 * */

define("MASTER_DIR", realpath(__DIR__));
require MASTER_DIR . '/vendor/autoload.php';


/*
 * Set timezone to mexico
 * */
date_default_timezone_set("America/Mexico_City");


/*
 * App core includes
 * */
use lib\Bin\App;
use lib\Database\StackPDO;
use lib\Data\SettingsManager;
use Phine\Path\Path;

/*
 * Set-up parameters
 * */
$settings = new SettingsManager(include(Path::join([MASTER_DIR, 'support', 'config.php'])));

$settings->bind('/^DIRS\.(.*)/', function ($property) {
    if (is_string($property))
    {
        $property = preg_replace("/\%/", MASTER_DIR, $property);
    }
    return preg_replace("/([\/\\\\])/", DIRECTORY_SEPARATOR, $property);
});

$pdo = new StackPDO(
    $settings->get('SQLSRV.hosting'),
    $settings->get('SQLSRV.username'),
    $settings->get('SQLSRV.password')
);

/*
 * Instance and return
 * */
$app = new App($pdo);
return $app;