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
 * Min length rule.
 *
 * @author Joe J. Howard
 */
class MinLength extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['minLength'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) >= $this->getParameter('minLength');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of The "%1$s" field must be at least %2$s characters long.', $field, $this->getParameter('minLength'));
	}
}
