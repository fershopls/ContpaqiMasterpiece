<?php

$app = require_once (realpath(__DIR__) . '/bootstrap.php');

// Include Modules
use SUA\SUA;

// Time to run
$app->run(SUA::class, [
    'regpat' => 'Z3418645100',
    'date_type' => 'B', // B = BIMESTRAL, M = MENSUAL
    'date_m' => 2,
    'date_y' => 2016
]);