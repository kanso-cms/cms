<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

/**
 * With parameters interface.
 *
 * @author Joe J. Howard
 */
interface WithParametersInterface
{
	/**
	 * Sets the validation rule parameters.
	 *
	 * @param array $parameters Parameters
	 */
	public function setParameters(array $parameters);
}
