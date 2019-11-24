<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\view;

use kanso\framework\common\ArrayAccessTrait;

/**
 * View abstract.
 *
 * @author Joe J. Howard
 */
abstract class ViewBase
{
	use ArrayAccessTrait;

	/**
	 * Array of files to include when rendering.
	 *
	 * @var array
	 */
	protected $includes = [];

    /**
     * Constructor.
     *
     * @param array $data Assoc array of variables to pass to the template
     */
    public function __construct(array $data = [])
    {
    	$this->data = $data;
    }
}
