<?php

$app = require_once (realpath(__DIR__) . '/bootstrap.php');

// Include Modules
use LIR\LIR;

// Time to run
$app->run(LIR::class, [
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