<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace kanso\framework\validator\rules;

use kanso\framework\validator\rules\traits\DoesntValidateWhenEmptyTrait;

/**
 * Base rule.
 *
 * @author Frederic G. Østby
 */
abstract class Rule
{
	use DoesntValidateWhenEmptyTrait;
}
