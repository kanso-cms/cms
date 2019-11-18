<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response\exceptions;

use RuntimeException;
use Throwable;

/**
 * Request exception.
 *
 * @author Joe J. Howard
 */
class RequestException extends RuntimeException
{
	/**
	 * Constructor.
	 *
	 * @param int             $code     Exception code
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(int $code, string $message = null, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
