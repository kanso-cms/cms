<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\ValidatesWhenEmptyTrait;

use function in_array;
use function sprintf;

/**
 * Required rule.
 *
 * @author Frederic G. Østby
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
		return sprintf('The %1$s field is required.', $field);
	}
}
