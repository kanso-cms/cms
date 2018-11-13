<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace kanso\framework\validator\rules;

use function preg_match;
use function sprintf;

/**
 * Alphanumeric dash rule.
 *
 * @author Frederic G. Østby
 */
class AlphanumericDash extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return preg_match('/^[a-z0-9_-]+$/i', $value) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain only numbers, letters and dashes.', $field);
	}
}
