<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands; 

use kanso\framework\git\Command;

/**
 * Git config command
 *
 * @see  https://git-scm.com/docs/git-config
 * @author Joe J. Howard
 */
class ConfigCommand extends Command
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
        # Are we not doing some sort of other 
        # operation we will list the the config
        if (empty($options) && empty($params))
        {
            $options = ['list', 'null'];
        }

        # Run the command
        $output = $this->run('config', [$options, $params]);

        if (!$this->is_successful())
        {
            return false;
        }

        # If we're not getting the config just return
        # whether the command was successfull or not
        if (!in_array('list', $options))
        {
            return $this->is_successful();
        }

        # Parse the result
        $config = [];
        $lines  = preg_split('/\0/', rtrim($output), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($lines as $line)
        {
            list($name, $value) = explode("\n", $line, 2);

            if (isset($config[$name]))
            {
                $config[$name] .= "\n" . $value;
            } 
            else
            {
                $config[$name] = $value;
            }
        }

        return $config;
    }
}
