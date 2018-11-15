<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Urldecode.
 *
 * @author Joe J. Howard
 */
class UrlDecode extends FilterBase implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return urldecode($value);
	}
}
