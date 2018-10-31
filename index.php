<?php

/**
 * Configure PHP error reporting.
 * @see http://php.net/manual/en/function.error-reporting.php
 */
error_reporting(E_ALL | E_STRICT);

/*
 * Choose if errors that are NOT caught by the Kanso error and exception handlers should be
 * printed to the screen as part of the output or if they should be hidden from the user.
 * It is recommended to set this value to false when you are in production.
 * @see http://php.net/manual/en/errorfunc.configuration.php#ini.display-errors
 */
ini_set('display_errors', 0);

/*
 * Override the default path for error logs. Again this is will only be used if
 * error_reporting is enabled. It will also only log errors NOT caught by
 * the Kanso error and exception handlers.
 */
ini_set('error_log', dirname(__FILE__) . '/app/storage/logs/' . date('d_m_y') . '_php_errors.log');

/**
 * Start using Kanso.
 */
require_once('app/bootstrap.php');
