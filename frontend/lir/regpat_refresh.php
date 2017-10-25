<?php
// Init Script
require_once realpath(__DIR__) . '/../../bootstrap.php';
$request_file = $settings->get('DIRS.request').DIRECTORY_SEPARATOR.$settings->get('APPS.LIR.request_regpat_file');
file_put_contents($request_file, '{"update":true,"time":'.time().'}')
?>
<script>window.history.back();</script>
