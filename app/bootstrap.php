<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Istanbul');

session_start();

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('DATA_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'data');

require APP_PATH . DIRECTORY_SEPARATOR . 'helpers.php';
require APP_PATH . DIRECTORY_SEPARATOR . 'database.php';
require APP_PATH . DIRECTORY_SEPARATOR . 'Auth.php';

ensure_database();
