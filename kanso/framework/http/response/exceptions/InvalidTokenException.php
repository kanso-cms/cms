<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response\exceptions;

use Throwable;

/**
 * 498 Exception.
 *
 * @author Joe J. Howard
 */
class InvalidTokenException extends RequestException
{
	/**
	 * Constructor.
	 *
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = null, Throwable $previous = null)
	{
		parent::__construct(498, $message, $previous);
	}
}
