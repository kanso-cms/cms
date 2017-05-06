<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Http\Response\Exceptions;

use Throwable;
use Kanso\Framework\Http\Response\Exceptions\RequestException;

/**
 * 498 Exception
 *
 * @author Joe J. Howard
 */
class InvalidTokenException extends RequestException
{
	/**
	 * Constructor.
	 *
	 * @access public
	 * @param string     $message  Exception message
	 * @param \Throwable $previous Previous exception
	 */
	public function __construct(string $message = null, Throwable $previous = null)
	{
		parent::__construct(498, $message, $previous);
	}
}
