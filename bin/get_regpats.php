<?php
/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

use SUA\Bin\GetDatabaseRegpat;

$dbs = $app->run(GetDatabaseRegpat::class);

$result = [];
foreach ($dbs as $db_slug => $_regpat)
{
    foreach ($_regpat as $regpat)
    {
        $result[$regpat] = $regpat;
    }
}

file_put_contents(MASTER_DIR . '/frontend/regpats.json', json_encode($result, JSON_PRETTY_PRINT));