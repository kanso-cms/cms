<?php

use kanso\framework\autoload\Autoloader;
use kanso\Kanso;

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
	define('KANSO_DIR', dirname(dirname(__FILE__)) . '/kanso');
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

/**
 * Kanso uses a cascading file-system to load configuration
 * files. This means you can run Kanso under different 'environments',
 * by creating a new directory and placing any configuration files in
 * there. Then define the environment below as the directory.
 * @see https://github.com/phanan/cascading-config
 */

// Uncomment this to add the sanbox config environment
/*
if (!defined('KANSO_ENV'))
{
	define('KANSO_ENV', 'sandbox');
}
*/

/**
 * ---------------------------------------------------------
 * AUTOLOADING
 * ---------------------------------------------------------.
 */

/**
 * Register Kanso autoloader
 * If you are using composer's autoloader you should remove this.
 */
require_once KANSO_DIR . '/framework/autoload/Autoloader.php';

$autoloader = new Autoloader;

$autoloader->addPrefix('kanso', KANSO_DIR);

$autoloader->addPrefix('app', APP_DIR);

$autoloader->addPrefix('Braintree', dirname(KANSO_DIR) . '/vendor/braintree/braintree_php/lib/Braintree');

$autoloader->addPrefix('Sinergi\BrowserDetector', dirname(KANSO_DIR) . '/vendor/sinergi/browser-detector/src');

$autoloader->register();

/**
 * Composer autoloader
 * Use this if you are using composer and comment out the default
 * autoloader above.
 */
// include dirname(__DIR__) . '/vendor/autoload.php';

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
