<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\validator\filters;

use kanso\framework\validator\filters\traits\DoesntFilterWhenUnset;

/**
 * Boolean filter.
 *
 * @author Joe J. Howard
 */
abstract class FilterBase
{
	use DoesntFilterWhenUnset;
}