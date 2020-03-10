<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

use kanso\framework\validator\filters\traits\FiltersWhenUnset;

/**
 * Boolean filter.
 *
 * @author Joe J. Howard
 */
class BoolVal extends FilterBase implements FilterInterface
{
	use FiltersWhenUnset;

	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		if (is_null($value) || $value === false)
		{
			return false;
		}
		elseif (is_bool($value))
		{
			return boolval($value);
		}
		elseif (is_int($value))
		{
			return boolval($value);
		}
		elseif (is_float($value))
		{
			return floatval($value) > 0;
		}
		elseif (is_numeric($value))
		{
			return intval($value) > 0;
		}
		elseif (is_string($value))
		{
			$value = trim(strtolower($value));

			if ($value === 'yes' || $value === 'on' || $value === 'true' || $value === '1')
			{
				return true;
			}
			elseif ($value === 'no' || $value === 'off' || $value === 'false' || $value === '0' || $value === '-1' || $value === '')
			{
				return false;
			}

			return false;
		}

		return boolval($value);
	}
}
