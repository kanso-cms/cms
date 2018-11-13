<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\WithParametersTrait;

use function sprintf;

/**
 * Match rule.
 *
 * @author Frederic G. Østby
 */
class Match extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['field'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return $this->getParameter('field') === $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must did not match.', $field, $this->parameters['field']);
	}
}
