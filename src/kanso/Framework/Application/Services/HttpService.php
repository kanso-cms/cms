<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\Framework\Utility\Str;
use Kanso\Framework\Http\{
	Request\Environment,
	Request\Headers as RequestHeaders,
	Request\Request,
	Response\Protocol,
	Response\Format,
	Response\Body,
	Response\Status,
	Response\Headers as ResponseHeaders,
	Response\CDN,
	Response\Cache,
	Response\Response,
	Route\Router,
	Cookie\Cookie,
	Cookie\Storage\NativeCookieStorage,
	Session\Session,
	Session\Store as SessionStore,
	Session\Flash,
	Session\Token
};


/**
 * HTTP services
 *
 * @author Joe J. Howard
 */
class HttpService extends Service
{
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
     * @return \Kanso\Framework\Http\Cookie\Storage\NativeCookieStorage
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
     * @return \Kanso\Framework\Http\Response\Cache
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
     * @return \Kanso\Framework\Http\Response\CDN
     */
	private function getCDN(): CDN
	{
		return new CDN($this->container->Request->environment()->HTTP_HOST, $this->container->Config->get('cdn.host'), $this->container->Config->get('cdn.enabled'));
	}

	/**
     * Get the HTTP response protocol
     *
     * @access private
     * @return \Kanso\Framework\Http\Response\Protocol
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
}
