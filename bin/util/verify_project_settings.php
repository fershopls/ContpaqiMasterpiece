<?php
/*
 * This script should be included from bootstrap.php to work properly
 * */
use lib\Data\ConfigDirectoryManager;

$configDirectoryPath = MASTER_DIR . '/config';
$settings = new ConfigDirectoryManager($configDirectoryPath);

// Create every directory if not exists
foreach ($settings->get('dirs') as $key => $path)
{
    $path = get_dir($key, $settings);
    if (!file_exists($path) || !is_dir($path))
    {
        mkdir($path, 0777, true);
        echo "\n[SETTINGS] Creating missing directory {$path}";
    }
}

// Credentials file
$file = MASTER_DIR.'/config/sqlsrv.php';
if (!file_exists($file))
{
    echo "\n[SETTINGS] Creating missing file {$file}";
    file_put_contents($file, "<?php\nreturn array(\n'hosting'=>'',\n'username'=>'',\n'password'=>'benjo',\n);");
}

return $settings;