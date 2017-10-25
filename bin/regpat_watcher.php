<?php
/* @var \lib\Bin\App $app*/
$app = isset($app)?$app:require_once(realpath(__DIR__) . '/../bootstrap.php');

$request_file = $settings->get('DIRS.request').DIRECTORY_SEPARATOR.$settings->get('APPS.LIR.request_regpat_file');
$default = array('time'=>time());

if (file_exists($request_file))
{
    // Open
    $req = json_decode(file_get_contents($request_file), true);
    $req = array_merge([], $default, $req);
    if (isset($req['opened']))
    {
        // Alredy opened
        echo PHP_EOL."LIR regpat request json file already working with. This could mean crash.";
        $req = false;
    } else {
        $req['opened'] = time();
        file_put_contents($request_file, json_encode($req, JSON_PRETTY_PRINT));
    }
} else {
    $req = false;
}

// No esta jalando esto
if ($req)
{
    echo PHP_EOL.'[REGPATS] Updating regpats by user request on '.date('His-mdY', $req['time']).'...';
    require_once 'get_regpats.php';
    var_dump($req);
    echo PHP_EOL.'Done.'.PHP_EOL;
    $req['time_done'] = time();
    file_put_contents($request_file, json_encode($req, JSON_PRETTY_PRINT));
}