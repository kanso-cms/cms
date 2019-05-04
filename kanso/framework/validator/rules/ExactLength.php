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
 * Exact length rule.
 *
 * @author Joe J. Howard
 */
class ExactLength extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['length'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) === intval($this->getParameter('length'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of The "%1$s" field must be exactly %2$s characters long.', $field, $this->getParameter('length'));
	}
}
