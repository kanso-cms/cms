<?php

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('UTC');

setlocale(LC_ALL, 'C');

mb_language('uni');

mb_regex_encoding('UTF-8');

mb_internal_encoding('UTF-8');

/*
 * Path to the Kanso core directory.
 * This is REQUIRED for the application to function
 * properly.
 */
define('KANSO_DIR', dirname(__FILE__, 2) . '/kanso');

/*
 * Path to the Kanso app directory.
 * This is REQUIRED for the application to function
 * properly.
 */
define('APP_DIR', dirname(__FILE__, 2) . '/app');

/*
 * Kanso env for testing
 */
define('KANSO_ENV', 'tests');

require_once dirname(__DIR__) . '/vendor/autoload.php';
