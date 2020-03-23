<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response\exceptions;

use Throwable;

/**
 * Ajax Exception.
 *
 * @author Joe J. Howard
 */
class AjaxPostException extends RequestException
{
	/**
	 * Constructor.
	 *
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = null, Throwable $previous = null)
	{
		$message = empty($message) ? 'The request must be bade over AJAX.' : $message;

		parent::__construct(400, $message, $previous);
	}
}
