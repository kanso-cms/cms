<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\WithParametersTrait;

use function mb_strlen;
use function sprintf;

/**
 * Max length rule.
 *
 * @author Joe J. Howard
 */
class MaxLength extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['maxLength'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) <= $this->getParameter('maxLength');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be at most %2$s characters long.', $field, $this->parameters['maxLength']);
	}
}
