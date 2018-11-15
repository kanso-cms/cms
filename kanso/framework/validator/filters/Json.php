<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Json decode.
 *
 * @author Joe J. Howard
 */
class Json extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return json_decode($value, true);
	}
}
