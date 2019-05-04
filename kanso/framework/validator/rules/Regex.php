<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\WithParametersTrait;

use function preg_match;
use function sprintf;

/**
 * Regex rule.
 *
 * @author Joe J. Howard
 */
class Regex extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['regex'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return preg_match($this->getParameter('regex'), $value) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of The "%1$s" field does not match the required format.', $field);
	}
}
