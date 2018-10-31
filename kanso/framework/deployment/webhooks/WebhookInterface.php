<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\deployment\webhooks;

use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;

/**
 * Frameowrk deployment interface.
 *
 * @author Joe J. Howard
 */
interface WebhookInterface
{
	/**
	 * Validate the incoming webhook.
	 *
	 * @param  kanso\framework\http\request\Request         $request  Request object
	 * @param  Responkanso\framework\http\response\Response $response Response object
	 * @param  Closure                                      $next     Next middleware layer
	 * @return bool
	 */
	public function validate(): bool;

    /**
     * Update the framework.
     *
     * @access public
     */
    public function deploy();

	/**
	 * Returns the incoming webhook event.
	 *
	 * @return string
	 */
	public function event(): string;

	/**
	 * Returns the incoming webhook event.
	 *
	 * @return string
	 */
	public function payload(): array;
}
