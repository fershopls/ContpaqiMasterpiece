<?php

/* @var \lib\Bin\App $app*/
$app = require_once(realpath(__DIR__) . '/../bootstrap.php');

use lib\Data\RequestsManager;
use Phine\Path\Path;
use lib\Database\SQLite3\Analytics;

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
        
        // DEBUG
        $id = isset($app_config['id'])&&is_numeric($app_config['id'])&&$app_config['id']!=0?$app_config['id']:null;
        $ana = new Analytics($settings->get('DIRS.cache'));
        if ($id)
        {
            // Created in frontend
            $obj = $ana->get($id);
            $obj['status_start'] = 'Created in frontend. ';
        } else {
            // Created god knows where
            $obj = $ana->create([
                'frontend_id' => strtoupper($app_slug),
                'created_at' => time(),
                'status_start' => 'ID not found. Created in backend. ',
            ]);
            $id = $obj['id'];
        }
        $app_config['id'] = $id;
        $app_config['__cache_dir'] = $settings->get('DIRS.cache');
        //
        $obj['started_at'] = time();
        $obj['params_backend'] = json_encode($app_config);
        $status_start = $obj['status_start'];
        $ana->update($obj['id'], $obj);

        // Run app
        echo "\n[{$app_slug}] Running with \n====\n". json_encode($app_config, JSON_PRETTY_PRINT)."\n====\n";
        $app->run($app_details['class'], $app_config);
        $requestHandler->delete();

        // Send email..
        if ($app_config['email'] != '') {
            echo "\n\n[MAIL] Sending mail to '{$app_config['email']}'.";
            $asunto = 'Reporte "'.$app_config['filename'].'" Generado';
            $mensaje = "Su reporte \"{$app_config['filename']}\" se ha generado en \\\\192.168.2.200\\{$app_output_path}\\{$output_filename}.";
            $cabeceras = 'From: no-reply@gmail.com' . "\r\n".
                'Reply-To: desarrollo@global-systems.mx' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            if(mail($app_config['email'], $asunto, $mensaje, $cabeceras)) {
                echo "\n[MAIL] Sended.";
                $status_start .= 'Mail Done. ';
            } else {
                echo "\n[MAIL] Error.";
                $status_start .= 'Mail Sending Error to "'.$app_config['email'].'". ';
            }
        } else {
            $status_start .= 'Empty mail. ';
        }

        $obj = $ana->get($obj['id']);
        $obj['status_start'] = $status_start;
        $obj['ended_at'] = time();
        $ana->update($obj['id'], $obj);
    } else {
        echo "\n[{$app_slug}] No request found. Skipping.";
    }
}