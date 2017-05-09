<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\application\services\Service;
use kanso\framework\utility\Str;
use kanso\framework\http\request\Environment;
use kanso\framework\http\request\Headers as RequestHeaders;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Protocol;
use kanso\framework\http\response\Format;
use kanso\framework\http\response\Body;
use kanso\framework\http\response\Status;
use kanso\framework\http\response\Headers as ResponseHeaders;
use kanso\framework\http\response\CDN;
use kanso\framework\http\response\Cache;
use kanso\framework\http\response\Response;
use kanso\framework\http\route\Router;
use kanso\framework\http\cookie\Cookie;
use kanso\framework\http\cookie\storage\NativeCookieStorage;
use kanso\framework\http\session\Session;
use kanso\framework\http\session\Store as SessionStore;
use kanso\framework\http\session\Flash;
use kanso\framework\http\session\Token;

/**
 * HTTP services
 *
 * @author Joe J. Howard
 */
class HttpService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->registerRequest();

		$this->registerCookie();
		
		$this->registerSession();

		$this->registerResponse();

		$this->registerRouter();
	}

	/**
     * Registers the Request object
     *
     * @access private
     */
	private function registerRequest()
	{
		$this->container->singleton('Request', function ()
		{
			return new Request(new Environment, new RequestHeaders);
		});
	}

	/**
     * Registers the cookie object
     *
     * @access private
     */
	private function registerCookie()
	{
		$this->container->singleton('Cookie', function ()
		{
			$cookieConfiguration = $this->container->Config->get('cookie.configurations.'.$this->container->Config->get('cookie.configuration'));

			$store = $this->loadCookieStore($cookieConfiguration);

			return new Cookie($store, $cookieConfiguration['name'], $cookieConfiguration['expire']);
		});
	}

	/**
     * Loads the cookie storage implementation
     *
     * @access private
     * @param  array   $cookieConfiguration Cookie configuration to use
     * @return mixed
     */
	private function loadCookieStore(array $cookieConfiguration)
	{
		$storeConfig = $cookieConfiguration['storage'];

		if ($storeConfig['type'] === 'native')
		{
			return $this->nativeCookieStore($storeConfig, $cookieConfiguration);
		}
	}

	/**
     * Loads the cookie storage implementation
     *
     * @access private
     * @param  array   $storeConfig         Configuration for the storage
     * @param  array   $cookieConfiguration Configuration for cookie sending/reading
     * @return \kanso\framework\http\cookie\storage\NativeCookieStorage
     */
	private function nativeCookieStore(array $storeConfig, array $cookieConfiguration): NativeCookieStorage
	{
		return new NativeCookieStorage($this->container->Crypto, $cookieConfiguration);
	}

	/**
     * Registers the session object
     *
     * @access private
     */
	private function registerSession()
	{
		$this->container->singleton('Session', function ()
		{
			$sessionConfiguration = $this->container->Config->get('session.configurations.'.$this->container->Config->get('session.default'));

			return new Session(new SessionStore, New Token, new Flash, $sessionConfiguration);
		});
	}

	/**
     * Get the HTTP Response cache
     *
     * @access private
     * @return \kanso\framework\http\response\Cache
     */
	private function getCache(): Cache
	{
		$key = Str::alphaDash($this->container->Request->path());

		return new Cache($this->container->Cache, $key, $this->container->Config->get('cache.http_cache_enabled'));
	}

	/**
     * Get the HTTP response CDN
     *
     * @access private
     * @return \kanso\framework\http\response\CDN
     */
	private function getCDN(): CDN
	{
		return new CDN($this->container->Request->environment()->HTTP_HOST, $this->container->Config->get('cdn.host'), $this->container->Config->get('cdn.enabled'));
	}

	/**
     * Get the HTTP response protocol
     *
     * @access private
     * @return \kanso\framework\http\response\Protocol
     */
	private function getProtocol(): Protocol
	{
		return new Protocol($this->container->Request->environment()->HTTP_PROTOCOL);
	}

	/**
     * Registers the response object
     *
     * @access private
     */
	private function registerResponse()
	{
		$this->container->singleton('Response', function ()
		{
			return new Response($this->getProtocol(), new Format, new Body, new Status, new ResponseHeaders, $this->container->Cookie, $this->container->Session, $this->getCache(), $this->getCDN(), $this->container->View);
		});
	}

	/**
     * Registers the router object
     *
     * @access private
     */
	private function registerRouter()
	{
		$this->container->singleton('Router', function ($container)
		{
			return new Router($container->Request, $container->Onion);
		});
	}	
}