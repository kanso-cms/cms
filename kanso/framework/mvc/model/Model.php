<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\model;

use kanso\Kanso;
use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Base Model
 *
 * @author Joe J. Howard
 */
abstract class Model
{
	use ContainerAwareTrait;

	/**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
    }
}