<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands; 

use kanso\framework\git\Command;

/**
 * Git stage command
 *
 * @see  https://git-scm.com/docs/git-stage
 * @author Joe J. Howard
 */
class StageCommand extends Command
{
	/**
     * Magic method invoke
     * 
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return bool
     */
    public function __invoke(array $options = [], array $params = []): bool
    {
        # Run the command
        $output = $this->run('stage', [$options, $params]);

        return $this->is_successful();
    }
} 