<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters\traits;

/**
 * Boolean filter.
 *
 * @author Joe J. Howard
 */
trait DoesntFilterWhenUnset
{
	/**
	 * {@inheritdoc}
	 */
	public function filterWhenUnset(): bool
	{
		return false;
	}
}
