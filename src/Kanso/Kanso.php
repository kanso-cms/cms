<?php

namespace Kanso;

/**
 * Kanso
 *
 * @property Container      \Kanso\Helper\Container
 * @property Config         array
 * @property Environment    array
 * @property Headers        array
 * @property Request        \Kanso\Http\Request
 * @property Response       \Kanso\Http\Response
 * @property Database       \Kanso\Database\Database
 * @property Gatekeeper     \Kanso\Auth\Gatekeeper
 * @property Bookkeeper     \Kanso\Articles\Bookkeeper
 * @property Router         \Kanso\Router\Router
 * @property Mailer         \Kanso\Utility\Mailer
 * @property Query          \Kanso\View\Query
 * @property View           \Kanso\View\View
 * @property Cache          \Kanso\Cache\Cache
 * @property Events         \Kanso\Events
 * @property Filters        \Kanso\Filters
 * @property Cookie         \Kanso\Storage\Cookie
 * @property Admin          \Kanso\Admin\Admin
 * @property MediaLibray    \Kanso\Media\MediaLibray
 *
 */
class Kanso 
{

	/**
	 * @var \Kanso\Helper\Container
	 */
	public $Container;

	/**
	 * @var string    The Kanso application version
	 */
	public $Version = '0.0.045';

	/**
	 * @var string    The Kanso application name
	 */
	protected $name;

	/**
	 * @var string    The body for a 500 Server Error
	 */
	protected $onServerError;

	/**
	 * @var array     List of classes loaded by Kanso
	 */
	protected static $loadedClasses = [];

	/**
	 * @var array[\Kanso]
	 */
	protected static $apps = [];

	/**
	 * @var boolean    Is Kanso currently installed ?
	 */
	protected $isInstalled;

	
	/********************************************************************************
	* PSR-0 AUTOLOADER
	*
	* Do not use if you are using Composer to autoload dependencies.
	*******************************************************************************/

	/**
	 *
	 * Kanso PSR-0 autoloader
	 */
	public static function autoload($className)
	{

		$thisClass = str_replace(__NAMESPACE__.'\\', '', __CLASS__);
		$baseDir   = __DIR__;
		if (substr($baseDir, -strlen($thisClass)) === $thisClass) {
			$baseDir = substr($baseDir, 0, -strlen($thisClass));
		}
		$className = ltrim($className, '\\');
		$fileName  = $baseDir;
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		if (file_exists($fileName)) {
			require $fileName;
			self::$loadedClasses[$className] = $fileName;
		}

	}

	/**
	 *
	 * Register Kanso's PSR-0 autoloader
	 */
	public static function registerAutoloader()
	{
		spl_autoload_register(__NAMESPACE__ . "\\Kanso::autoload");
	}

	/**
	 * Returns the list of classes, interfaces, and traits loaded by the
	 * autoloader.
	 *
	 * @return array An array of key-value pairs where the key is the class
	 * or interface name and the value is the file name.
	 *
	 */
	public static function getLoadedClasses()
	{
		return self::$loadedClasses;
	}


	/********************************************************************************
	* INSTANTIATION AND CONFIGURATION
	*******************************************************************************/

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{		
		# Check if the apllication is installed
		$this->isInstalled = !file_exists(__DIR__.'/Install.php') && file_exists(__DIR__.'/Config.php');

		# Halt if the application is not installed
		if (!$this->isInstalled) return;

		# Setup IoC container
		$this->Container = new \Kanso\Helper\Container();

		# Default settings  
		$this->Container->singleton('Settings', function () {
			return new \Kanso\Config\Settings();
		});

		# Initialize application configuration  
		$this->Container['Config'] = $this->Settings->get();

		# Default environment
		$this->Container->singleton('Environment', function () {
			return \Kanso\Environment::extract();
		});

		# Default headers
		$this->Container->singleton('Headers', function () {
			return \Kanso\Http\Headers::extract();
		});

		# Default request
		$this->Container->singleton('Request', function () {
			return new \Kanso\Http\Request();
		});

		# Default response
		$this->Container->singleton('Response', function () {
			return new \Kanso\Http\Response();
		});

		# Default view
		$this->Container->singleton('View', function () {
			return new \Kanso\View\View();
		});

		# Default cookie
		$this->Container->singleton('Cookie', function () {
			return new \Kanso\Storage\Cookie();
		});

		# Default session
		$this->Container->singleton('Session', function () {
			return new \Kanso\Storage\Session();
		});

		# Default databse
		$this->Container->singleton('Database', function () {
			return new \Kanso\Database\Database;
		});

		# Default router
		$this->Container->singleton('Router', function () {
			return new \Kanso\Router\Router();
		});

		# Default Query
		$this->Container->singleton('Query', function () {
			return new Query\Query();
		});

		# Default Events
		$this->Container->singleton('Events', function () {
			return \Kanso\Events::getInstance();
		});

		# Default Filters
		$this->Container->singleton('Filters', function () {
			return \Kanso\Filters::getInstance();
		});

		# Default Cache
		$this->Container->singleton('Cache', function () {
			return new \Kanso\Cache\Cache();
		});

		# Default GUMP
		$this->Container->set('Validation', function() {
			return new \Kanso\Utility\GUMP();
		});

		# Default Gatekeeper
		$this->Container->singleton('Gatekeeper', function () {
			return new \Kanso\Auth\Gatekeeper();
		});

		# Default Articlekeeper
		$this->Container->singleton('Bookkeeper', function () {
			return new \Kanso\Articles\Bookkeeper();
		});

		# Default MediaLibrary
		$this->Container->singleton('MediaLibrary', function () {
			return new \Kanso\Media\MediaLibrary();
		});

		# Default Comment manager
		$this->Container->singleton('Comments', function () {
			return new \Kanso\Comments\CommentManager();
		});

		# Default FileSystem
		$this->Container->singleton('FileSystem', function () {
			return new \Kanso\Utility\FileSystem();
		});

		# Default Humanizer
		$this->Container->singleton('Humanizer', function () {
			return new \Kanso\Utility\Humanizer();
		});

		# Default Mailer
		$this->Container->singleton('Mailer', function () {
			return new \Kanso\Utility\Mailer();
		});

		# Default Admin
		$this->Container->singleton('Admin', function () {
			return new \Kanso\Admin\Admin();
		});

		# Make default if first instance
		if (is_null(static::getInstance())) {
			$this->setName('default');
		}

		# Default this is not an admin request
		$this->is_admin = false;

		# Start the session
		$this->Session->init();

		# Include the active theme's plugins.php file if it exists
		$plugins = $this->Environment['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'plugins.php';
		if (file_exists($plugins)) require_once($plugins);
	}

	public function __get($name)
	{
		return $this->Container->get($name);
	}

	public function __set($name, $value)
	{
		$this->Container->set($name, $value);
	}

	public function __isset($name)
	{
		return $this->Container->has($name);
	}

	public function __unset($name)
	{
		$this->Container->remove($name);
	}

	/**
	 * Get application instance by name
	 *
	 * @param  string    $name    The name of the Kanso application
	 * @return \Kanso\Kanso|null
	 */
	public static function getInstance($name = 'default')
	{
		return isset(static::$apps[$name]) ? static::$apps[$name] : null;
	}

	/**
	 * Set Kanso application name
	 *
	 * @param  string    $name    The name of this Kanso application
	 */
	public function setName($name)
	{
		$this->name = $name;
		static::$apps[$name] = $this;
	}

	/**
	 * Get Kanso application name
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set a config key/par but dont save
	 *
	 * @return string|null
	 */
	public function tmpConfig($key, $value)
	{
		$config = $this->Config;
		$config[$key] = $value;
		$this->Container->set('Config', $config);
	}

	/********************************************************************************
	* PUBLIC ROUTER METHODS
	*******************************************************************************/

	/**
	 * Add GET route
	 *
	 * @see     Kanso\Router\Router->call()
	 * @param   string $route
	 * @param   mixed  $callback
	 * @param   mixed  $args (optional)
	 */
	public function get($route, $callback, $args = null) 
	{
		$this->Router->call('get', $route, $callback, $args);
	}

	/**
	 * Add POST route
	 *
	 * @see     Kanso\Router\Router->call()
	 * @param   string $route
	 * @param   mixed  $callback
	 * @param   mixed  $args (optional)
	 */
	public function post($route, $callback, $args = null) 
	{
		$this->Router->call('post', $route, $callback, $args);
	}

	/**
	 * Add PUT route
	 *
	 * @see     Kanso\Router\Router->call()
	 * @param   string $route
	 * @param   mixed  $callback
	 * @param   mixed  $args (optional)
	 */
	public function put($route, $callback, $args = null) 
	{
		$this->Router->call('put', $route, $callback, $args);
	}

	/**
	 * Add DELETE route
	 *
	 * @see     Kanso\Router\Router->call()
	 * @param   string $route
	 * @param   mixed $callback
	 * @param   mixed $args (optional)
	 */
	public function delete($route, $callback, $args = null) 
	{
		$this->Router->call('delete', $route, $callback, $args);
	}

	/**
	 * Add OPTIONS route
	 *
	 * @see     Kanso\Router\Router->call()
	 * @param   string $route
	 * @param   mixed $callback
	 * @param   mixed $args (optional)
	 */
	public function options($route, $callback, $args = null) 
	{
		$this->Router->call('options', $route, $callback, $args);
	}

	/**
	 * Add HEAD route
	 *
	 * @see     Kanso\Router\Router->call()
	 * @param   string $route
	 * @param   mixed $callback
	 * @param   mixed $args (optional)
	 */
	public function head($route, $callback, $args = null) 
	{
		$this->Router->call('head', $route, $callback, $args);
	}

	/**
	 * Redirect
	 *
	 * This method immediately redirects to a new URL. By default,
	 * this issues a 302 Found response; this is considered the default
	 * generic redirect response. You may also specify another valid
	 * 3xx status code if you want. This method will automatically set the
	 * HTTP Location header for you using the URL parameter.
	 *
	 * @param  string   $url        The destination URL
	 * @param  int      $status     The HTTP redirect status code (optional)
	 */
	public function redirect($url, $status = 302)
	{
		# Call the pre dispatch event
		\Kanso\Events::fire('redirect', [$this->Environment['REQUEST_URI'], $url, $status, time()]);
		
		# Redirect via the HTTP Response object
		$this->Response->redirect($url, $status);

		# Write to the session
		$this->Session->save();

		# Stop Kanso
		$this->halt($status);
	}

	/**
	 * Halt
	 *
	 * Stop the application and immediately send the response with a
	 * specific status and body to the HTTP client. This may send any
	 * type of response: info, success, redirect, client error, or server error.
	 * If you need to render a template AND customize the response status,
	 * use the application's `render()` method instead.
	 *
	 * @param  int      $status     The HTTP response status
	 * @param  string   $message    The HTTP response body (optional)
	 */
	public function halt($status, $message = '')
	{
		$this->cleanBuffer();
		$this->Response->setStatus($status);
		$this->Response->setBody($message);
		$this->stop();
	}

	/********************************************************************************
	* APPLICATION ACCESSORS
	*******************************************************************************/

	/**
	 * Get a reference to the Config array
	 *
	 * @return array
	 */
	public function Config()
	{
		return $this->Config;
	}

	/**
	 * Get a reference to the Settings object
	 *
	 * @return array
	 */
	public function Settings()
	{
		return $this->Settings;
	}

	/**
	 * Get a reference to the Environment array
	 *
	 * @return \Kanso\Environment::extract();
	 */
	public function Environment()
	{
		return $this->Environment;
	}

	/**
	 * Get the Request object
	 *
	 * @return \Kanso\Http\Request
	 */
	public function Request()
	{
		return $this->Request;
	}

	/**
	 * Get the Response object
	 *
	 * @return \Kanso\Http\Response
	 */
	public function Response()
	{
		return $this->Response;
	}

	/**
	 * Get the headers object
	 *
	 * @return \Kanso\Http\Headers
	 */
	public function Headers()
	{
		return $this->Headers;
	}

	/**
	 * Get the Router object
	 *
	 * @return \Kanso\Router
	 */
	public function Router()
	{
		return $this->Router;
	}

	/**
	 * Get a new GUMP object
	 *
	 * @return \Kanso\Utility\GUMP
	 */
	public function Validation()
	{
		return $this->Validation;
	}

	/**
	 * Get the Query object
	 *
	 * @return \Kanso\Router
	 */
	public function Query()
	{
		return $this->Query;
	}

	/**
	 * Get the Query object
	 *
	 * @return \Kanso\Router
	 */
	public function View()
	{
		return $this->View;
	}

	/**
	 * Get the Cache object
	 *
	 * @return \Kanso\Router
	 */
	public function Cache()
	{
		return $this->Cache;
	}

	/**
	 * Get the Database object
	 *
	 * @return \Kanso\Database\Databse
	 */
	public function Database()
	{
		return $this->Database;
	}

	/**
	 * Get the Gatekeeper object
	 *
	 * @return \Kanso\Auth\GateKeeper
	 */
	public function Gatekeeper()
	{
		return $this->Gatekeeper;
	}

	/**
	 * Get the Bookkeeper object
	 *
	 * @return \Kanso\Auth\Session
	 */
	public function Bookkeeper()
	{
		return $this->Bookkeeper;
	}

	/**
	 * Get the Bookkeeper object
	 *
	 * @return \Kanso\Comments\CommentManager
	 */
	public function Comments()
	{
		return $this->Comments;
	}

	/**
	 * Get the Cookie manager
	 *
	 * @return \Kanso\Storage\Cookie
	 */
	public function Cookie()
	{
		return $this->Cookie;
	}

	/**
	 * Get the session manager
	 *
	 * @return \Kanso\Storage\Session
	 */
	public function Session()
	{
		return $this->Session;
	}

	/**
	 * Get the MediaLibrary manager
	 *
	 * @return \Kanso\Media\MediaLibrary
	 */
	public function MediaLibrary()
	{
		return $this->MediaLibrary;
	}

	/******************************************************************************
	* RENDERING
	*****************************************************************************/

	/**
	 * Render a template
	 *
	 * Call this method within a GET, POST, PUT, PATCH, DELETE, NOT FOUND, or ERROR
	 * router callable to render a template whose output is appended to the
	 * current HTTP response body. How the template is rendered is
	 * delegated to the current View.
	 *
	 * @param  string $template    The absolute path of to the file passed into the view's render() method
	 * @param  array  $data        Associative array of data made available to the view (optional)
	 * @param  int    $status      The HTTP response status code to use (optional)
	 */
	public function render($template, $data = null, $status = null)
	{
		# Set the status
		if ($status) $this->Response->setStatus($status);

		# Set the data
		if ($data) $this->View->setMultiple($data);

		# Append and parse template file
		$this->Response->appendBody($this->View->display($template));
	}

	/********************************************************************************
	* RUNNER
	*******************************************************************************/

	/**
	 * Run
	 *
	 * This method dispatches the Router and the core Kanso application.
	 * The result is an array of HTTP status, header, and body. These three items
	 * are returned to the HTTP client.
	 */
	public function run() 
	{

		# Set the error handler
		set_error_handler(['\Kanso\Kanso', 'handleErrors']);

		# Validate the application is installed
		if (!$this->isInstalled) {

			$this->runInstall();

			return;
		}

		# Set the default error callback
		$this->Router->error([$this, 'notFound']);
		
		# Apply default application routes
		$this->setDefaultRoutes();

		# Call the pre dispatch event
		\Kanso\Events::fire('preDispatch', $this->Environment['REQUEST_URI']);

		# Dispatch the router
		$this->Router->dispatch();

		# Get headers, body, status
		list($status, $headers, $body, $cookies) = $this->Response->finalize();

		# Write to the session
		$this->Session->save();

		# Call the mid dispatch event
		\Kanso\Events::fire('midDispatch', [$status, $headers, $body]);

		# Send headers and cookies
		$this->Response->sendheaders();

		# Send body, but only if it isn't a HEAD request
		if (!$this->Request->isHead()) {
			
			echo $body;
			
			# Save the output to the cache if the request is cachable
			if ($this->Config['KANSO_USE_CACHE'] === true && $this->Query->is_single() && !$this->Cache->has()) {
				$this->Cache->put($body);
			}	
		}

		# Call the post dispatch event
		\Kanso\Events::fire('postDispatch');

		# Restore the default error handler
		restore_error_handler();
	}

	/**
	 * Run the install script
	 *
	 * This method initializes the install script
	 * and is called from $Kanso->run() when the application
	 * is not installed.
	 */
	private function runInstall()
	{
		# Default is to do nothing
		$validRequest = false;

		# Get the installation directory
        $installDir = rtrim(dirname(__FILE__), '/');

        # Get the request URL
        $requestUrl = rtrim($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], '/');

        # Convert the requested url to a directory
        $dirRequest = str_replace($_SERVER['HTTP_HOST'], $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'Kanso', $requestUrl);

        # Validate the request is for the location
        # of the installation
        if ($dirRequest === $installDir) {

        	# Validate the install file exists
        	if (!file_exists(__DIR__.'/Install.php')) {
        		throw new \Exception("Could not install Kanso. Install.php was not found on the server. Ensure you have renamed \"Install.sample.php\" to \"Install.php\".");        		
        	}

        	# Install the application if needed
			$installer = new \Kanso\Install\Installer();
			$installed = $installer->installKanso();

			# Admin path for scripts and favicons
    		$adminAssetsPth = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__).DIRECTORY_SEPARATOR.'Admin').'/assets/';

			# Show the welcome page
			include "Install/InstallSplash.php";
		
        }
		
		# Return the aplication was not installed
	}


	/********************************************************************************
	* ERROR HANDLING AND DEBUGGING
	*******************************************************************************/
	
	/**
	 * Stop
	 *
	 * The thrown exception will be caught in application's `call()` method
	 * and the response will be sent as is to the HTTP client.
	 *
	 * @throws \Kanso\Exception\Stop
	 */
	public function stop()
	{
		throw new \Kanso\Exception\Stop();
	}

	/**
	 * Clean current output buffer
	 */
	protected function cleanBuffer()
	{
		if (ob_get_level() !== 0) {
			ob_clean();
		}
	}

	/**
	 * Convert errors into ErrorException objects
	 *
	 * This method catches PHP errors and converts them into \ErrorException objects;
	 * these \ErrorException objects are then thrown and caught by Kanso's
	 * built-in or custom error handlers.
	 *
	 * @param  int            $errno   The numeric type of the Error
	 * @param  string         $errstr  The error message
	 * @param  string         $errfile The absolute path to the affected file
	 * @param  int            $errline The line number of the error in the affected file
	 * @return bool
	 * @throws \ErrorException
	 */
	public static function handleErrors($errno, $errstr = '', $errfile = '', $errline = '')
	{
		# Are we reporting errors ?
		if (!($errno & error_reporting()))  return;
		
		# Fire the error event
		\Kanso\Events::fire('error', [$errstr, $errno, $errfile, $errline, time()]);

		# Throw a kanso exception
		throw new \Kanso\Exception\Error($errstr, $errno, $errfile, $errline);
	}

	/**
	 * Default Not Found handler
	 *
	 * This method is always called directly from the router when no
	 * route was found and a 404 response needs to be sent.
	 */
	public function notFound() 
	{
		# Call the pre dispatch event
		\Kanso\Events::fire('notFound', [$this->Environment['REQUEST_URI'], time()]);

		# Set the default body
		$body = '<html><head><title>404 Not Found</title></head><body><H1>Not Found</H1><p>The requested document was not found on this server.</P><hr></body></html>';
		
		# Check that the current theme has a 404 page
		# Set the body to the 404 template
		$errorDoc = $this->Environment['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'404.php';
		if (file_exists($errorDoc)) $body = $this->View->display($errorDoc);

		# Ajax and requests don't need a body
		# just a 404
		if ($this->Request->isAjax()) $body = '';

		# Set the resoponse and body
		$this->Response->setStatus(404);
		$this->Response->setBody($body);
	}

	/**
	 * Default Server Error
	 *
	 * Used internally or externally to display a generic 500 server error.
	 */
	public function serverError($body = null) 
	{
		if ($this->onServerError) $body = $this->onServerError;
		if (!$body) $body = '<html><head><title>500 Internal Server Error</title></head><body><h1>Internal Server Error</h1>The server encountered an internal error ormisconfiguration and was unable to completeyour request.<p>Please contact the server administrator to inform of the time the error occurredand of anything you might have done that may havecaused the error.</p><p>More information about this error may be availablein the server error log.</p><p></p><hr></body></html>';
		$this->Response->setStatus(500);
		$this->Response->setBody($body); 
	}


	/********************************************************************************
	* APPLICATION ROUTES
	*******************************************************************************/

	/**
	 * Set the default application routes
	 *
	 * Note that most controllers are kept as string and loaded directly in the router
	 * when they need to be. This keeps the router from being bloated and ensures
	 * speedy lookups 
	 *
	 */
	private function setDefaultRoutes() 
	{
		require 'Router'.DIRECTORY_SEPARATOR.'Routes.php';
	}

	/********************************************************************************
	* TEMPLATE LOADING
	*******************************************************************************/

	/**
	 * Load Kanso Theme templates
	 *
	 * This method is called when the router matches a wildcard and needs
	 * to load a template from the current theme. 
	 *
	 * Note that if caching is enabled, Kanso\Cache\Cache checks if it can load from the 
	 * cache when the current request is made. However if another user is in the admin area
	 * at the same time and deletes the cache file between the time this request was made
	 * and the time the cached file is loaded, the cached file won't exist anymore. 
	 * The chances of this actually happening are incredibly low (almost impossible). 
	 * However, we need to take it into consideration. The fix is storing the loadFromCache() 
	 * body as a variable. If it returns false, it means the file was deleted. We can then load 
	 * the page directly from the template instead.
	 *
	 * @param string Page type declaration
	 */
	public static function loadTemplate($pageType) 
	{
		# Get the Kanso Object instance
		$_this = self::getInstance();

		# Check if we can load from cache
		$_this->Cache->setPageType($pageType);

		# Can we load from the cache
		$fromCache = false;

		if ($pageType === 'single' && $_this->Config['KANSO_USE_CACHE'] === true && $_this->Cache->has()) {
			if ($_this->Cache->expired($_this->Config['KANSO_CACHE_LIFE'])) {
				$_this->Cache->remove();
				$fromCache = false;
			}
			else {
				$fromCache = true;
			}
		}
		if ($fromCache) {
			$_this->Response->setBody($_this->Cache->get());
		}
		# Otherwise load the template file
		else {

			# Filter the posts based on the pageType
			$_this->Query->filterPosts($pageType);

			# Load the appropriate template into the view/body
			$template = $_this->getTemplate($pageType);

			# If the status was set to 404 here by the query, stop rendering
			# Note this does not send a 404 straight away. If you have a custom route
			# and wanted to display a template, you could still change the the status/response
			# between now and when kanso sends a response.
			if ($_this->Response->getstatus() !== 404 && $template) {

				# Render the template
				$_this->render($template);
			
			}

		}

	}

	/**
	 * Determine what template to use
	 *
	 * @param  string Page type declaration
	 * @return string
	 */
	private function getTemplate($pageType) 
	{
		# Waterfall of pages
		$waterfall    =  [];
		
		# Current theme dir
		$templateBase = $this->Environment['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR;

		# Explode request url
		$urlParts     = array_filter(explode('/', trim($this->Environment['REQUEST_URI'], '/')));
		
		if ($pageType === 'home') {
			$waterfall[] = 'homepage';
			$waterfall[] = 'index';
		}
		else if ($pageType === 'page') {
			$waterfall[] = 'page-'.array_pop($urlParts);
			$waterfall[] = 'page';
		}
		else if ($pageType === 'single') {			
			$waterfall[] = 'single-'.array_pop($urlParts);
			$waterfall[] = 'single';
		}
		else if (\Kanso\Utility\Str::getBeforeFirstChar($pageType, '-') === 'single') {
			if ($this->Query->have_posts()) {
				$waterfall[] = 'single-'.$this->Query->the_post_type();
			}
			$waterfall[] = 'single-'.array_pop($urlParts);
			$waterfall[] = 'single';
		}
		else if ($pageType === 'archive') {
			$waterfall[] = 'archive';
			$waterfall[] = 'index';
		}
		else if ($pageType === 'tag') {
			if ($this->Response->getstatus() !== 404) {
				$waterfall[] = 'tag-'.$this->Query->the_taxonomy()['slug'];
			}
			$waterfall[] = 'taxonomy-tag';
			$waterfall[] = 'tag';
			$waterfall[] = 'taxonomy';
		}
		else if ($pageType === 'category') {
			if ($this->Response->getstatus() !== 404) {
				$waterfall[] = 'category-'.$this->Query->the_taxonomy()['slug'];
			}
			$waterfall[] = 'taxonomy-category';
			$waterfall[] = 'category';
			$waterfall[] = 'taxonomy';
		}
		else if ($pageType === 'author') {
			if ($this->Response->getstatus() !== 404) {
				$waterfall[] = 'author-'.$this->Query->the_taxonomy()['slug'];
			}
			$waterfall[] = 'taxonomy-author';
			$waterfall[] = 'author';
			$waterfall[] = 'taxonomy';
		}
		else if ($pageType === 'search') {
			$waterfall[] = 'search';
			$waterfall[] = 'index';
		}

		foreach ($waterfall as $name) {
			$template = "$templateBase$name.php";
			if (file_exists($template)) return $template;
		}

		if (file_exists("$templateBase$pageType.php")) {
			return "$templateBase$pageType.php";
		}

		return false;
	}

	/********************************************************************************
	* ADDITIONAL FEATURES
	*******************************************************************************/

	/**
	 * Load RSS Feed
	 *
	 * This function is called directly from the router 
	 * when an RSS request for the homepage/single is made.
	 *
	 */
	public static function loadRssFeed($pageType) 
	{
		# Get the Kanso Object instance
		$_this = self::getInstance();

		# Filter the posts based on the pageType
		$_this->Query->filterPosts($pageType);

		# If there are no posts and this is not a request for the
		# homepage or search pages - retrun a 404 
		if (!$_this->Query->have_posts()) {
			
			$_this->notFound();
			
		}

		# Otherwise load the appropriate post
		else {
			
			# Set the content type to XML
			$_this->Response->setheaders(['Content-Type' => 'application/rss+xml']);

			$_this->Response->setBody(\Kanso\RSS\rssBuilder::buildFeed($pageType));

		}
	}

	/**
	 * Load the sitemap
	 *
	 * This function is called directly from the router 
	 * when a sitemap request is made.
	 *
	 */
	public static function loadSiteMap() 
	{
		# Get the Kanso Object instance
		$_this = self::getInstance();

		# Set the content type to XML
		$_this->Response->setheaders(['Content-Type' => 'text/xml']);

		# Load the sitemap into the body
		$_this->Response->setBody(\Kanso\Sitemap\SitemapGenerator::buildSiteMap());
	}

	/**
	 * Load the openSearch XML
	 *
	 * This function is called directly from the router 
	 * when a XML request for opensearch.
	 *
	 */
	public static function loadOpenSearch()
	{
		# Get the Kanso Object instance
		$_this = self::getInstance();

		# Set the content type to XML
		$_this->Response->setheaders(['Content-Type' => 'text/xml']);

		# Save the XML
		$xml = '
		<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
			<ShortName>'.$_this->Config['KANSO_SITE_TITLE'].'</ShortName>
			<LongName>'.$_this->Config['KANSO_SITE_TITLE'].' Search</LongName>
			<Description>Search through articles posted on '.$_this->Config['KANSO_SITE_TITLE'].'</Description>
			<InputEncoding>UTF-8</InputEncoding>
			<OutputEncoding>UTF-8</OutputEncoding>
			<AdultContent>false</AdultContent>
			<Language>en-us</Language>
			<Developer>'.$_this->Config['KANSO_OWNER_USERNAME'].'</Developer>
			<Contact>'.$_this->Config['KANSO_OWNER_EMAIL'].'</Contact>
			<Attribution>Search data from '.$_this->Config['KANSO_SITE_TITLE'].' '.$_this->Environment['HTTP_HOST'].'</Attribution>
			<SyndicationRight>open</SyndicationRight>
			<Query role="example" searchTerms="Apple"/>
			<Url type="text/html" template="'.$_this->Environment['HTTP_HOST'].'/search-results/?q={searchTerms}"/>
		</OpenSearchDescription>
		';

		# Load the XML into the body
		$_this->Response->setBody($xml);
	}

}