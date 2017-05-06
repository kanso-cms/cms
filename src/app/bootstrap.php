<?php

use Kanso\Framework\Autoload\Autoloader;
use Kanso\Kanso;

/**
 * ---------------------------------------------------------
 * ERROR HANDLING
 * ---------------------------------------------------------
 */

/**
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

/**
 * ---------------------------------------------------------
 * APPLICATION CONTANTS
 * ---------------------------------------------------------
 */

/**
 * Path to the Kanso application directory.
 * This is REQUIRED for the application to function
 * properly.
 */
define('KANSO_DIR', dirname(dirname(__FILE__)).'/Kanso');

/**
 * This constant is not actually required for the application
 * to function properly, however it is used in the configuration
 * files.
 */
define('APP_DIR', dirname(__FILE__));

/**
 * Path to the Kanso configuration directory.
 * This is REQUIRED for the application to function
 * properly. This where the configuration files are 
 * stored.
 */
define('CONFIG_DIR', APP_DIR.'/configurations');

/**
 * Kanso uses a cascading file-system to load configuration
 * files. This means you can run Kanso under different 'environments',
 * by creating a new directory and placing any configuration files in
 * there. Then define the environment below as the directory
 * @see https://github.com/phanan/cascading-config
 */
# define('KANSO_ENV', 'sandbox');

/**
 * ---------------------------------------------------------
 * AUTOLOADING
 * ---------------------------------------------------------
 */

/**
 * Register Kanso autoloader
 * If you are using composer's autoloader you should remove this.
 */
require_once 'Kanso/Framework/Autoload/AutoLoader.php';

$autoloader = new Autoloader;

$autoloader->addPrefix('Kanso', KANSO_DIR);

$autoloader->register();

/**
 * Composer autoloader
 * Use this if you are using composer and comment out the default
 * autoloader above.
 */
# include dirname(__DIR__) . '/vendor/autoload.php';

/**
 * ---------------------------------------------------------
 * INSTANTIATION
 * ---------------------------------------------------------
 */
/**
 * Instantiate and initialize the Kanso application
 */
$kanso = Kanso::instance();

/**
 * Any middleware should go below, i.e after Kanso has been
 * initialized but before any routes are dispatched.
 */

/**
 * Run Kanso
 */
$kanso->run();
