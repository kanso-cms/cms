<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\plugin;

use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Plugin base class.
 *
 * @author Joe J. Howard
 */
abstract class Plugin
{
	use ContainerAwareTrait;

	/**
	 * Install the plugin.
	 *
	 * @access public
	 */
	public function install();

	/**
	 * Checks if this plugin is installed.
	 *
	 * @access public
	 * @return bool
	 */
	public function installed(): bool;
}
