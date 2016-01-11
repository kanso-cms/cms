<?php
/**
 * Kanso Core Initialization File
 *
 * This is Kanso's core initializion file. Every Kanso 
 * installation must have this file or an equivlent which 
 * points to Kanso installation. 
 *
 * All HTTP requests must be sent to this file 
 * for Kanso to function correctly. This can be done by 
 * including the following code in your htaccess file
 *
 * <IfModule mod_rewrite.c>
 *
 * 		Options +FollowSymlinks
 *		
 *		RewriteEngine on
 *		RewriteBase /
 *		
 *		RewriteCond %{REQUEST_FILENAME} !-f
 *		RewriteCond %{REQUEST_FILENAME} !-d
 *		RewriteRule ^(.*)$ index.php?/$1 [L]
 *
 *		RewriteCond %{REQUEST_METHOD} =GET
 *	    RewriteRule ^Kanso/.*$ index.php?$1
 *
 * </IfModule>
 *
 * A sample htaccess file is included in Kanso's 
 * installation package.
 */
 
# Set the value error reporting configuration to E_ALL
# on PHP's ini
# Remove this if you don't want php to output runtime
# errors to HTTP client.
ini_set('display_errors', 1);


# Set the error_reporting directive at runtime
# Remove this if you don't want php to output runtime
# errors to HTTP client.
error_reporting(E_ALL);


# Set the default timezone. Adjust to your own timezone
# if desired
date_default_timezone_set('America/New_York');

# Require Kanso's main class file
require_once 'Kanso/Kanso.php';

# Register Kanso's auto-loader
# This should be removed if you are using Composer's autoloader
\Kanso\Kanso::registerAutoloader();

# Create a new Kanso object
$Kanso = new Kanso\Kanso();

# Initialize and run Kanso
$Kanso->run();
