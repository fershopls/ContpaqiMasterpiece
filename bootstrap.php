<?php

// Autoload
define("MASTER_DIR", realpath(__DIR__));
require MASTER_DIR . '/vendor/autoload.php';

// Set timezone
date_default_timezone_set("America/Mexico_City");

// Include some classes
use lib\Bin\App;
use lib\Database\StackPDO;

// Config things
$settings = require(MASTER_DIR . '/bin/util/verify_project_settings.php');
function get_dir ($key, $settings, $default = "")
{
    return str_replace('%', MASTER_DIR, $settings->get('DIRS.'.$key, $default));
};

// Connect to SQL Manager
$pdo = new StackPDO(
    $settings->get('SQLSRV.hosting'),
    $settings->get('SQLSRV.username'),
    $settings->get('SQLSRV.password')
);

// Return
$app = new App($pdo);
return $app;