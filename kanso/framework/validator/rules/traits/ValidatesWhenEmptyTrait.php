<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules\traits;

/**
 * Validates when empty trait.
 *
 * @author Joe J. Howard
 */
trait ValidatesWhenEmptyTrait
{
	/**
	 * {@inheritdoc}
	 */
	public function validateWhenEmpty(): bool
	{
		return true;
	}
}
