<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Trim.
 *
 * @author Joe J. Howard
 */
class Trim extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return trim($value);
	}
}
