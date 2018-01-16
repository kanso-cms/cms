<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands; 

use kanso\framework\git\Command;

/**
 * Git init command
 *
 * @see  https://git-scm.com/docs/git-init
 * @author Joe J. Howard
 */
class InitCommand extends Command
{
    /**
     * Magic method invoke
     * 
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return bool
     */
    public function __invoke(string $directory = null, array $option = []): bool
    {
    	# Set the working repo
    	if ($directory)
    	{
    		$this->git->setDirectory($directory);
    	}

        # Run the command
        $output = $this->run('init', [$options]);

        return $this->is_successful();
    }
}

