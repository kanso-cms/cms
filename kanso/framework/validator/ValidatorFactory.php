<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator;

use kanso\framework\ioc\Container;

/**
 * Validator factory.
 *
 * @author Joe J. Howard
 */
class ValidatorFactory
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container|null
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Creates and returns a validator instance.
	 *
	 * @param  array                                $input Array to validate
	 * @param  array                                $rules Array of validation rules
	 * @return \kanso\framework\validator\Validator
	 */
	public function create(array $input, array $rules, array $filters = []): Validator
	{
		return new Validator($input, $rules, $filters, $this->container);
	}
}
