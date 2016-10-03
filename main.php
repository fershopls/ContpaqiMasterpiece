<?php

require_once (realpath(__DIR__) . '/bootstrap.php');

// Include App Core
use lib\Bin\App;
use lib\Database\StackPDO;
use lib\Data\SettingsManager;
use Phine\Path\Path;

// Include Modules
use SUA\Reporter\SUA;

// Initialize Variables
$settings = new SettingsManager(include(Path::join([MASTER_DIR, 'support', 'config.php'])));
$pdo = new StackPDO(
    $settings->get('SQLSRV.hosting'),
    $settings->get('SQLSRV.username'),
    $settings->get('SQLSRV.password')
);

// Instance Class
$app = new App($pdo);
// Añadir Modulos
$app->add(SUA::class);
// Despegar
$app->run([
    'regpat' => 'Z3418645100',
    'date_m' => 2,
    'date_y' => 2016
]);