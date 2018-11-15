<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\WithParametersTrait;

use function sprintf;

/**
 * Less than or equal to rule.
 *
 * @author Joe J. Howard
 */
class LessThanOrEqualTo extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['lessThanOrEqualTo'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value <= $this->getParameter('lessThanOrEqualTo');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than or equal to %2$s.', $field, $this->parameters['lessThanOrEqualTo']);
	}
}
