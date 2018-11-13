<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace kanso\framework\validator\rules;

use function preg_match;
use function sprintf;

/**
 * Float rule.
 *
 * @author Frederic G. Østby
 */
class FloatingPoint extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return preg_match('/(^(\-?)0\.\d+$)|(^(\-?)[1-9]\d*\.\d+$)/', $value) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a float.', $field);
	}
}
