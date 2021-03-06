<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response\exceptions;

use Throwable;

/**
 * 404 Exception.
 *
 * @author Joe J. Howard
 */
class NotFoundException extends RequestException
{
	/**
	 * Constructor.
	 *
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = null, Throwable $previous = null)
	{
		$message = empty($message) ? 'The requested URL was not found on this server.' : $message;

		parent::__construct(404, $message, $previous);
	}
}
