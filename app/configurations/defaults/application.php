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
	'secret' => 'gav9JtvLCangs9EblRKF7jpTobFyjrDdnVgXhgifkHcgW2vuca1141VypG',
	
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
	 * Send response
	 * ---------------------------------------------------------
	 *
	 * This tells Kanso to automatically send the response body,
	 * headers, cookie, session etc.. on all incoming requests.
	 * This is the default behavior for both the framework and the cms. 
	 * This means that if the the router matches a route, the 
	 * route will change the response object - (e.g send a 200, 
	 * throw a 500 etc..), and if a route is not matched a 404 
	 * response is sent by default.
	 * 
	 * If you disable this, you will need to call 
	 * $kanso->Response->send() on all requests even on 404s.
	 * You should only disable this if you know what you're doing.
	 * 
	 */
	'send_response' => true,

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
			'\kanso\framework\application\services\SecurityService',
			'\kanso\framework\application\services\CacheService',
			'\kanso\framework\application\services\HttpService',
			'\kanso\framework\application\services\OnionService',
			'\kanso\framework\application\services\DatabaseService',
			'\kanso\framework\application\services\MVCService',
            '\kanso\framework\application\services\UtilityService',
			'\kanso\framework\application\services\ErrorHandlerService',
		],

		/**
		 * Adds the CMS to the core framework. If you don't want to use CMS, 
		 * you could remove these and only use the core.
		 */
		'cms' =>
		[
			'\kanso\cms\application\services\AccessService',
			'\kanso\cms\application\services\WrapperService',
			'\kanso\cms\application\services\GatekeeperService',
			'\kanso\cms\application\services\InstallerService',
			'\kanso\cms\application\services\EmailService',
			'\kanso\cms\application\services\QueryService',
			'\kanso\cms\application\services\EventService',
			'\kanso\cms\application\services\AdminService',
			'\kanso\cms\application\services\BootService',
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
