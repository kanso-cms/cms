<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

use kanso\framework\validator\filters\traits\FiltersWhenUnset;

/**
 * Integer.
 *
 * @author Joe J. Howard
 */
class Integer extends FilterBase implements FilterInterface
{
	use FiltersWhenUnset;

	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return $value === '' ? null : intval($value);
	}
}
