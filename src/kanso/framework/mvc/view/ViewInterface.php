<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\view;

/**
 * View interface
 *
 * @author Joe J. Howard
 */
interface ViewInterface
{
	/**
	 * Add a file to include when rendering
	 *
	 * @access public
	 */
	public function include(string $file);

	/**
	 * Add multiple files to include when rendering
	 *
	 * @access public
	 */
	public function includes(array $files);

    /**
	 * Render the view and return the output
	 *
	 * @access public
	 * @param  string $file Absolute path to file to render
	 * @param  aray   $data Array of variables to extract
	 */
	public function display(string $file, array $data = []): string;
}
