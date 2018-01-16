<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands; 

use kanso\framework\git\Command;

/**
 * Git archive command
 *
 * @see  https://git-scm.com/docs/git-branch
 * @author Joe J. Howard
 */
class BranchCommand extends Command
{
    /**
     * Magic method invoke
     * 
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return string|array|false 
     */
    public function __invoke(array $options = [], array $params = [])
    {
        $show_list = false;
        
        # If no options were provided, we're returning a list
        # of branches
        if (empty($options) && empty($params))
        {
            $options = ['v', 'abbrev' => 7];
            
            $show_list = true;
        }

        # Run the command
        $output = $this->run('branch', [$options, $params]);

        if (!$this->is_successful())
        {
            return false;
        }

        # if we're not bulding a list return if the 
        # command was successful or not
        if (!$show_list)
        {
            return $output;
        }

        $branches = [];
        
        # Buld the list of branches
        $lines = preg_split('/\r?\n/', rtrim($output), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($lines as $line)
        {
            $branch = array();
            preg_match('/(?<current>\*| ) (?<name>[^\s]+) +((?:->) (?<alias>[^\s]+)|(?<hash>[0-9a-z]{7}) (?<title>.*))/', $line, $matches);

            $branch['current'] = ($matches['current'] == '*');
            $branch['name']    = $matches['name'];

            if (isset($matches['hash']))
            {
                $branch['hash']  = $matches['hash'];
                $branch['title'] = $matches['title'];
            } 
            else {
                $branch['alias'] = $matches['alias'];
            }

            $branches[$matches['name']] = $branch;
        }

        usort($branches, function($a, $b) {
            return strnatcasecmp($a["name"], $b["name"]);
        });

        return $branches;
    }
}
