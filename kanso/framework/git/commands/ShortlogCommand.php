<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands; 

use kanso\framework\git\Command;

/**
 * Git shortlog command
 *
 * @see  https://git-scm.com/docs/git-shortlog
 * @author Joe J. Howard
 */
class ShortlogCommand extends Command
{
	/**
     * Magic method invoke
     * 
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return bool|string
     */
    public function __invoke(array $options = [], array $params = [])
    {
        # Run the command
        $output = $this->run('shortlog', [$options, $params]);

        if (!$this->is_successful())
        {
        	return false;
        }
        
        return $output;
    }
}