<?php

/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

use lib\Data\RequestsManager;

$apps = $settings->get('APPS', []);
$requestHandler = new RequestsManager();

foreach ($apps as $app_slug => $app_details)
{
    $app_request_path = $settings->get('DIRS.APPS.'.$app_slug, '');

    $app_config = $requestHandler->get($app_details['default'])->on($app_request_path);

    if ($app_config)
    {
        echo "\n[{$app_slug}] Running with \n====". json_encode($app_config, JSON_PRETTY_PRINT)."\n====";
        $app->run($app_details['class'], $app_config);
        $requestHandler->delete();
    } else {
        echo "\n[{$app_slug}] No request found. Skipping.";
    }
}


