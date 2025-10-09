<?php
// config/config.php

// App info

define('APP_NAME', 'Forex');
define('BASE_URL', 'http://localhost/forex');
define('TIMEZONE', 'Africa/Lagos');

// Set default timezone
date_default_timezone_set(TIMEZONE);

//error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/../app/helpers/Utils.php';
require_once __DIR__.'/../app/helpers/Format.php';

