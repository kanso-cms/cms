<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\model;

use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Base Model.
 *
 * @author Joe J. Howard
 */
abstract class Model
{
	use ContainerAwareTrait;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }
}
