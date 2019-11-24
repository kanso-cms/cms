<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\view;

/**
 * View interface.
 *
 * @author Joe J. Howard
 */
interface ViewInterface
{
	/**
	 * Add a file to include when rendering.
	 */
	public function include(string $file);

	/**
	 * Add multiple files to include when rendering.
	 */
	public function includes(array $files);

	/**
	 * Render the view and return the output.
	 *
	 * @param string $file Absolute path to file to render
	 * @param array  $data Array of variables to extract
	 */
	public function display(string $file, array $data = []): string;
}
