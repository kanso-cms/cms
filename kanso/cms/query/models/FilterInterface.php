<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Query filter interface.
 *
 * @author Joe J. Howard
 */
interface FilterInterface
{
	/**
	 * Filters the posts.
	 *
	 * @return bool
	 */
	public function filter(): bool;

	/**
	 * Get the request Type.
	 *
	 * @return string
	 */
	public function requestType(): string;
}
