<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response\exceptions;

use Throwable;

/**
 * POST Validation Exception.
 *
 * @author Joe J. Howard
 */
class PostValidationException extends RequestException
{
	/**
	 * Constructor.
	 *
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = null, Throwable $previous = null)
	{
		$message = empty($message) ? 'The request did not meet the validation requirements set by the server.' : $message;

		parent::__construct(401, $message, $previous);
	}
}
