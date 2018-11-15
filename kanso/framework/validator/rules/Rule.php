<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\DoesntValidateWhenEmptyTrait;

/**
 * Base rule.
 *
 * @author Joe J. Howard
 */
abstract class Rule
{
	use DoesntValidateWhenEmptyTrait;
}
