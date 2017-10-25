<?php
// Init Script
require_once realpath(__DIR__) . '/../../bootstrap.php';
$request_dir = $settings->get('DIRS.request');
file_put_contents($request_dir . '/regpat.json', '{"update":true,"time":'.time().'}')
?>
<script>window.history.back();</script>
