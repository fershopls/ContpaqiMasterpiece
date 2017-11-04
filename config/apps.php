<?php

return array (

    'SUA' => [
        'class' => '\SUA\Bin\SUA',
        'default' => array(
            'filename' => '',
            'regpat' => 'Z3418645100',
            'date_type' => 'B', // B = BIMESTRAL, M = MENSUAL
            'date_m' => 2,
            'date_y' => 2016,
            'email' => '',
        )
    ],
    
    'LIR' => [
        'class' => '\LIR\Bin\LIR',
        'request_regpat_file' => 'regpat.json',
        'default' => array(
            'filename' => '',
            'regpat' => 'Z3418645100',
            'database' => '',
            'exercise' => '2016',
            'period_type' => 'semanal',
            'date_begin'  => '20160101 00:00',
            'date_end'    => '20160331 00:00',
            'email' => '',
            'options' => [
                'worker_net' => false, // ask about this
                'worker_down' => true,
            ],
        ),
    ],

);