<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Numeric.
 *
 * @author Joe J. Howard
 */
class Numeric implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return preg_replace('/[^0-9]+/', '', $value);
	}
}
