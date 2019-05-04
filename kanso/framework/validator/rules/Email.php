<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use function filter_var;
use function sprintf;

/**
 * Email rule.
 *
 * @author Joe J. Howard
 */
class Email extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The "%1$s" field must contain a valid e-mail address.', $field);
	}
}
