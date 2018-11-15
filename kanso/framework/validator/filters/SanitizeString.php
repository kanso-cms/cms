<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Sanitize string.
 *
 * @author Joe J. Howard
 */
class SanitizeString extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return filter_var($value, FILTER_SANITIZE_STRING);
	}
}
