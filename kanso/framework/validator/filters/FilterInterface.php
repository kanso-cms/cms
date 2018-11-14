<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Filter interface.
 *
 * @author Joe J. Howard
 */
interface FilterInterface
{
	/**
	 * Filters the field value and returns result.
	 *
	 * @param  string $value Field value
	 * @return mixed
	 */
	public function filter(string $value);

	/**
	 * Pass through filter when field is not set
	 *
	 * @return bool
	 */
	public function filterWhenUnset(): bool;
}
