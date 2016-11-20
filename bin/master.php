<?php

/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

use lib\Data\RequestsManager;

$apps = $settings->get('APPS', []);
$requestHandler = new RequestsManager();

foreach ($apps as $app_slug => $app_details)
{
    $app_dirs = $settings->get('DIRS.APPS', []);
    $request_path = str_replace('%', MASTER_DIR, isset($app_dirs[$app_slug])?$app_dirs[$app_slug]:'');

    if (!file_exists($request_path))
        mkdir($request_path);

    $app_config = $requestHandler->get($app_details['default'])->on($request_path);

    if ($app_config)
    {
        echo "\n[{$app_slug}] Running with ". json_encode($app_config, JSON_PRETTY_PRINT);
        $app->run($app_details['class'], $app_config);
    } else {
        echo "\n[{$app_slug}] Skipping";
    }

    $requestHandler->delete();
}


