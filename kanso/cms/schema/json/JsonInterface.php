<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Json generator interface.
 *
 * @author Joe J. Howard
 */
interface JsonInterface
{
	/**
	 * Generate the JSON data for this component.
	 *
	 * @return array
	 */
	public function generate(): array;
}
