<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use function preg_match;
use function sprintf;

/**
 * Alpha rule.
 *
 * @author Joe J. Howard
 */
class AlphaSpace extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return preg_match('/^[a-z ]+$/i', $value) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The "%1$s" field must contain only letters and spaces.', $field);
	}
}
