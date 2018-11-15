<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\WithParametersTrait;

use function sprintf;

/**
 * Less than rule.
 *
 * @author Joe J. Howard
 */
class LessThan extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['lessThan'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value < $this->getParameter('lessThan');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than %2$s.', $field, $this->getParameter('lessThan'));
	}
}
