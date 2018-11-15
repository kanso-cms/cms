<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use function preg_match;
use function sprintf;

/**
 * Integer rule.
 *
 * @author Joe J. Howard
 */
class Integer extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return preg_match('/(^(\-?)0$)|(^(\-?)[1-9]\d*$)/', $value) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain an integer.', $field);
	}
}
