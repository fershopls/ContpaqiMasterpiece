<?php
// Init Script
require_once realpath(__DIR__) . '/../../bootstrap.php';
$request_file = get_dir('request', $settings).DIRECTORY_SEPARATOR.$settings->get('APPS.LIR.request_regpat_file');
file_put_contents($request_file, '{"update":true,"time":'.time().'}')
?>
<script>window.location = '/';</script>
