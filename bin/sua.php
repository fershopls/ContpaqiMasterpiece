<?php
/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

// Include Modules
use SUA\Bin\SUA;

// Time to run
$app->run(SUA::class, [
    'filename' => MASTER_DIR . '/SUA_FILE.csv',
    
    'regpat' => 'Z3418645100',
    'date_type' => 'B', // B = BIMESTRAL, M = MENSUAL
    'date_m' => 2,
    'date_y' => 2016
]);