<?php
/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

use LIR\Bin\GetAvailableDatabases;
use SUA\Bin\GetDatabaseRegpat;

$available_databases = $app->run(GetAvailableDatabases::class, array('regpat'=>null))['strings'];
$dbs = $app->run(GetDatabaseRegpat::class);
$result = [];
foreach ($dbs as $db_slug => $_regpat)
{
    if (isset($available_databases[$db_slug]))
        $available_databases[$db_slug] = array("string"=>$available_databases[$db_slug], "regpats" => $_regpat);

    foreach ($_regpat as $regpat)
    {
        $result[$regpat] = $regpat;
    }
}

file_put_contents($settings->get('DIRS.cache').'/regpats.json', json_encode($result, JSON_PRETTY_PRINT));

file_put_contents($settings->get('DIRS.cache').'/databases.json',json_encode($available_databases, JSON_PRETTY_PRINT));

//Redirección al menú de reporte
$post = isset($_POST["buff"])?true:false;

if($post){
    header("Location: ../frontend/LIR.php");
}
