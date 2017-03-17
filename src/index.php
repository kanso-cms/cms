<?php
/**
 * Kanso Core Instantiation File
 *
 * This is Kanso's core initialization file. Every Kanso 
 * installation must have this file or an equivalent which 
 * points to Kanso installation. 
 *
 * All HTTP requests must be sent to this file 
 * for Kanso to function correctly.
 * A sample .htaccess file is included in Kanso's 
 * installation package.
 */
 
# This determines whether errors should be printed to the 
# screen (HTTP RESPONSE OR BROWSER) as part of the output 
# or if they should be hidden from the user. 
/**
* @see http://php.net/manual/en/errorfunc.configuration.php#ini.display-errors
*/

# hide errors
# ini_set('display_errors', 0);

# Display errors
ini_set('display_errors', 1);

# The error_reporting() function sets the error_reporting directive at runtime. 
# PHP has many levels of errors (FATAL ERROR, WARNING, NOTICE et...), 
# using this function sets that level for the duration (runtime) of your script. 
# This determines what errors are reported (logged or otherwise)
/**
* @see http://php.net/manual/en/function.error-reporting.php
*/

# Turn off all error reporting
# error_reporting(0);

# Report simple running errors
# error_reporting(E_ERROR | E_WARNING | E_PARSE);

# Reporting E_NOTICE can be good too (to report uninitialized
# variables or catch variable name misspellings ...)
# error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

# Report all errors except E_NOTICE
# error_reporting(E_ALL & ~E_NOTICE);

# Report all PHP errors 
error_reporting(E_ALL);

# Set the default timezone. Adjust to your own timezone if desired
/**
* @see http://php.net/manual/en/timezones.php
*/
date_default_timezone_set('Australia/Melbourne');

# Require Kanso's main class file
require_once 'Kanso/Kanso.php';

# Register Kanso's auto-loader
# This should be removed if you are using Composer's autoloader
\Kanso\Kanso::registerAutoloader();

# Create a new Kanso object
$Kanso = new Kanso\Kanso();

# Any customizations should go here - i.e 
# after Kanso's instantiation but before any dispatching.
# To be more precise with customization you may use Kanso's
# events.

# Initialize and run Kanso
$Kanso->run();