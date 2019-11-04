<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\console;

/**
 * Command interface.
 *
 * @author Joe J. Howard
 */
interface CommandInterface
{
	/**
	 * Returns the command description.
	 *
	 * @return string
	 */
	public function getDescription(): string;

	/**
	 * Returns the command arguments.
	 *
	 * @return array
	 */
	public function getArguments(): array;
}
