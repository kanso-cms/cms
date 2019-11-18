<?php

use kanso\Kanso;

/*
 * ---------------------------------------------------------
 * PHP 7.3
 * ---------------------------------------------------------
 */

if (strpos(PHP_VERSION, '7.3') !== false)
{
	ini_set('pcre.jit', '0');
}

/*
 * ---------------------------------------------------------
 * ERROR HANDLING
 * ---------------------------------------------------------
 */

/*
 * Convert all errors to ErrorExceptions.
 */
set_error_handler(function($code, $message, $file, $line)
{
	if((error_reporting() & $code) !== 0)
	{
		throw new ErrorException($message, $code, 0, $file, $line);
	}

	return true;
});

/*
 * ---------------------------------------------------------
 * APPLICATION CONSTANTS
 * ---------------------------------------------------------
 */

/*
 * Path to the Kanso core directory.
 * This is REQUIRED for the application to function
 * properly.
 */
if (!defined('KANSO_DIR'))
{
	define('KANSO_DIR', dirname(__FILE__, 2) . '/kanso');
}

/*
 * Path to the Kanso app directory.
 * This is REQUIRED for the application to function
 * properly.
 */
if (!defined('APP_DIR'))
{
	define('APP_DIR', dirname(__FILE__));
}

/*
 * Kanso uses a cascading file-system to load configuration
 * files. This means you can run Kanso under different 'environments',
 * by creating a new directory and placing any configuration files in
 * there. Then define the environment below as the directory.
 * @see https://github.com/phanan/cascading-config
 */

// Auto environment aware based on host
/*if (!defined('KANSO_ENV'))
{
	define('KANSO_ENV', 'sandbox');
}*/

/**
 * ---------------------------------------------------------
 * AUTOLOADING
 * ---------------------------------------------------------.
 */

/**
 * Composer autoloader.
 *
 * You need to install composer to use the autoloader
 */
require_once(dirname(KANSO_DIR) . '/vendor/autoload.php');

/**
 * ---------------------------------------------------------
 * INSTANTIATION
 * ---------------------------------------------------------.
 */
/**
 * Instantiate and initialize the Kanso application.
 */
$kanso = Kanso::instance();

/**
 * Any middleware should go below, i.e after Kanso has been
 * initialized but before any routes are dispatched.
 */
require_once APP_DIR . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'routes.php';

/*
 * Run Kanso
 */
$kanso->run();
