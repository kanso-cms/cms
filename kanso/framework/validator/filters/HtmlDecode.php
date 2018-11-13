<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

/**
 * Html decode.
 *
 * @author Joe J. Howard
 */
class HtmlDecode implements FilterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function filter(string $value)
	{
		return html_entity_decode($value, ENT_QUOTES);
	}
}
