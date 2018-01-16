<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands; 

use kanso\framework\git\Command;

/**
 * Git tag command
 *
 * @see  https://git-scm.com/docs/git-tag
 * @author Joe J. Howard
 */
class TagCommand extends Command
{
    /**
     * Magic method invoke
     * 
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return bool|array
     */
    public function __invoke(array $options = [], array $params = [])
    {

        # Run the command
        $output = $this->run('tag', [$options, $params]);

        # if we're not bulding a list return if the 
        # command was successful or not
        if (!empty($params))
        {
            return $this->is_successful();
        }

        if (!$this->is_successful())
        {
            return false;
        }

        # Buld the list of branches
        $tags = preg_split('/\r?\n/', rtrim($output), -1, PREG_SPLIT_NO_EMPTY);

        usort($tags, function($a, $b)
        {
            return strnatcasecmp($a, $b);
        });

        return $tags;
    }
}
