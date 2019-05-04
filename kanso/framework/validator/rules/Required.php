<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use function in_array;

use kanso\framework\validator\rules\traits\ValidatesWhenEmptyTrait;
use function sprintf;

/**
 * Required rule.
 *
 * @author Joe J. Howard
 */
class Required extends Rule implements RuleInterface
{
	use ValidatesWhenEmptyTrait;

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return !in_array($value, ['', null, []], true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The "%1$s" field is required.', $field);
	}
}
