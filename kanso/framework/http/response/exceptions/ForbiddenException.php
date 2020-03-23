<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response\exceptions;

use Throwable;

/**
 * 403 Exception.
 *
 * @author Joe J. Howard
 */
class ForbiddenException extends RequestException
{
	/**
	 * Constructor.
	 *
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = null, Throwable $previous = null)
	{
		$message = !$message ? 'You are not authorized to access the requested resource.' : $message;

		parent::__construct(403, $message, $previous);
	}
}
