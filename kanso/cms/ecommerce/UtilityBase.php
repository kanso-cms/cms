<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use kanso\framework\common\SqlBuilderTrait;
use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Base Model.
 *
 * @author Joe J. Howard
 */
abstract class UtilityBase
{
    use ContainerAwareTrait;

    use SqlBuilderTrait;
}
