<?php
/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

use SUA\Bin\GetDatabaseRegpat;

$dbs = $app->run(GetDatabaseRegpat::class);

print_r($dbs);