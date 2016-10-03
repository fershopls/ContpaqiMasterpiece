<?php

/*
 * Import Composer Autoload
 * */

define("MASTER_DIR", realpath(__DIR__));
require MASTER_DIR . '/vendor/autoload.php';


/*
 * Set Timezone to Mexico
 * */
date_default_timezone_set("America/Mexico_City");