<?php

return
[
	/**
	 * ---------------------------------------------------------
	 * Error handling
	 * ---------------------------------------------------------
	 *
	 * Application error and exception handling options.
	 */
	'error_handler' =>
	[
		/**
		 * Configure the error reporting. This denotes the level of
		 * what errors are reported (logged and/or displayed) while Kanso is running.
		 * @see http://php.net/manual/en/function.error-reporting.php
		 */
		'error_reporting' => E_ALL | E_STRICT,

		/**
		 * Choose if errors that are caught by the Kanso error and exception handlers should be
		 * printed to the screen as part of the output or if they should be hidden from the user.
		 * It is recommended to set this value to false when you are in production.
		 * @see http://php.net/manual/en/errorfunc.configuration.php#ini.display-errors
		 */
		'display_errors' => true,

		/**
		 * The log path to where Kanso will save errors and exceptions to file.
		 * This directory must exist and be writable by PHP.
		 */
		'log_path' => APP_DIR . '/storage/logs',
	],

	/**
	 * ---------------------------------------------------------
	 * Secret
	 * ---------------------------------------------------------
	 *
	 * The secret is used to provide cryptographic signing, and should be set to a unique, unpredictable value.
	 * This is similar to a crypto graphic "salt".
	 * You should NOT use the secret included with the framework in a production environment!
	 */
	'secret' => 'a1fa8454045e579c1d840e0d919c8a62ce89f72b5fca28dd16a08e6458',
	
	/**
	 * ---------------------------------------------------------
	 * Timezone
	 * ---------------------------------------------------------
	 *
	 * Set the default timezone used by various PHP date functions.
	 * @see http://php.net/manual/en/timezones.php
	 */
	'timezone' => 'Australia/Melbourne',

	/**
	 * ---------------------------------------------------------
	 * Charset
	 * ---------------------------------------------------------
	 *
	 * Default character set used internally in the framework.
	 * @see http://php.net/manual/en/mbstring.supported-encodings.php
	 */
	'charset' => 'UTF-8',

	/**
	 * ---------------------------------------------------------
	 * Services
	 * ---------------------------------------------------------
	 *
	 * Services to register into the dependency injection container at boot time.
	 * Dependencies will be registered in the the order that they are defined.
	 * If you have your own services that you want to register, 
	 * you can put them under the 'app' or any other key you want to use.
	 */
	'services' =>
	[
		/**
		 * Services required for only the core framework. This will 
		 * result in the Kanso framework with out the CMS.
		 */
		'framework' =>
		[
			'\Kanso\Framework\Application\Services\SecurityService',
			'\Kanso\Framework\Application\Services\CacheService',
			'\Kanso\Framework\Application\Services\HttpService',
			'\Kanso\Framework\Application\Services\OnionService',
			'\Kanso\Framework\Application\Services\DatabaseService',
			'\Kanso\Framework\Application\Services\ViewService',
			'\Kanso\Framework\Application\Services\ErrorHandlerService',
		],

		/**
		 * Adds the CMS to the core framework. If you don't want to use CMS, 
		 * you could remove these and only use the core.
		 */
		'cms' =>
		[
			'\Kanso\CMS\Application\Services\WrapperService',
			'\Kanso\CMS\Application\Services\GatekeeperService',
			'\Kanso\CMS\Application\Services\InstallerService',
			'\Kanso\CMS\Application\Services\EmailService',
			'\Kanso\CMS\Application\Services\QueryService',
			'\Kanso\CMS\Application\Services\BootService',
		],

		/**
		 * Defines your own application dependencies here. E.g any thing 
		 * you would like loaded into the IoC container and/or booted at runtime.
		 */
		'app' =>
		[

		],
	],

	/**
	 * ---------------------------------------------------------
	 * Class aliases
	 * ---------------------------------------------------------
	 *
	 * The key is the alias and the value is the actual class.
	 */
	'class_aliases' =>
	[

	],
];
