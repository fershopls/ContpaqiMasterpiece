<?php

/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

use lib\Data\RequestsManager;
use Phine\Path\Path;

$apps = $settings->get('APPS', []);
$requestHandler = new RequestsManager();

foreach ($apps as $app_slug => $app_details)
{
    $app_request_path = $settings->get('DIRS.APPS.'.$app_slug, '');

    $app_config = $requestHandler->get($app_details['default'])->on($app_request_path);

    if ($app_config)
    {
        // Set filename to output file
        $app_output_path = Path::join([$settings->get('DIRS.output'), $app_slug]);
        $output_filename = isset($app_config['filename'])&&$app_config['filename']!=''?'_'.preg_replace("/(\s)/", '_', strtolower($app_config['filename'])):'';
        $output_filename = date("YmdHis").$output_filename.'.csv';
        $app_config['filename'] = Path::join([$app_output_path, $output_filename]);
        if (!file_exists($app_output_path) || !is_dir($app_output_path))
            mkdir($app_output_path, 0777, true);

        // Run app
        echo "\n[{$app_slug}] Running with \n====\n". json_encode($app_config, JSON_PRETTY_PRINT)."\n====\n";
        $app->run($app_details['class'], $app_config);
        $requestHandler->delete();
    } else {
        echo "\n[{$app_slug}] No request found. Skipping.";
    }
}


