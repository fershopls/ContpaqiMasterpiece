<?php

require_once (realpath(__DIR__) . '/bootstrap.php');

// Include App Core
use lib\Bin\App;
use lib\Database\StackPDO;
use lib\Data\SettingsManager;
use Phine\Path\Path;

// Include Modules
use LIR\LIR;

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
$app->add(LIR::class);
// Despegar
$app->run([
    'regpat' => 'Z3418645100',
    'exercise' => '2016',
    'period_type' => 'semanal',
    'date_begin'  => '20160101 00:00',
    'date_end'    => '20160701 00:00',
    'email' => '',
    'options' => [
        'worker_net' => false, // ask about this
        'worker_down' => false,
    ],
]);