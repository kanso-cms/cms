<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Filter email.
 *
 * @author Joe J. Howard
 */
class Email implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return filter_var($value, FILTER_SANITIZE_STRING);
	}
}
