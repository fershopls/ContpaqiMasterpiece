<?php
/* @var \lib\Bin\App $app*/
$app = isset($app)?$app:require_once(realpath(__DIR__) . '/../bootstrap.php');

use lib\Data\RequestsManager;
$requestHandler = new RequestsManager();
$config = $requestHandler->get(['update'=>true,'time'=>time()])->on($settings->get('DIRS.request'));

if ($config)
{
    echo PHP_EOL.'[REGPATS] Updating regpats by user request on '.date('His-mdY', $config['time']).'...';
    require_once 'get_regpats.php';
    $config['time_done'] = time();
    file_put_contents($settings->get('DIRS.request').DIRECTORY_SEPARATOR.$settings->get('APPS.LIR.request_regpat_file'), json_encode($config, JSON_PRETTY_PRINT));
}