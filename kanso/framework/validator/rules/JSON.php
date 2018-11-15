<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use function json_decode;
use function json_last_error;
use function sprintf;

/**
 * JSON rule.
 *
 * @author Joe J. Howard
 */
class JSON extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) === false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain valid JSON.', $field);
	}
}
