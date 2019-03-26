<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use function in_array;

use kanso\framework\validator\rules\traits\WithParametersTrait;
use function sprintf;

/**
 * Not in rule.
 *
 * @author Joe J. Howard
 */
class NotIn extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['values'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return !in_array($value, $this->getParameter('values'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field contains an invalid value.', $field);
	}
}
