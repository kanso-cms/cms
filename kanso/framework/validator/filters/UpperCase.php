<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Uppercase.
 *
 * @author Joe J. Howard
 */
class UpperCase implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return strtoupper($value);
	}
}
