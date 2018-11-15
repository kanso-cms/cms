<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules\traits;

/**
 * Doesn't validate when empty trait.
 *
 * @author Joe J. Howard
 */
trait DoesntValidateWhenEmptyTrait
{
	/**
	 * {@inheritdoc}
	 */
	public function validateWhenEmpty(): bool
	{
		return false;
	}
}
