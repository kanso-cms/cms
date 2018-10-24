<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands;

use kanso\framework\git\Command;

/**
 * Git merge command.
 *
 * @see  https://git-scm.com/docs/git-merge
 * @author Joe J. Howard
 */
class MergeCommand extends Command
{
    /**
     * Magic method invoke.
     *
     * @param  array      $options Command options (optional) (default [])
     * @param  array      $params  Command params  (optional) (default [])
     * @return bool|array
     */
    public function __invoke(array $options = [], array $params = [])
    {
        // Run the command
        $output = $this->run('merge', [$options, $params]);

        $result = $this->is_successful();

        if ($result)
        {
            return true;
        }

        $conflicts = [];
     	$lines 	   = array_map('trim', explode("\n", $output));

     	foreach ($lines as $line)
        {
     		if (preg_match("/^CONFLICT \([^\)]+\)/", $line))
            {
     			$line_conflict = explode(':', $line);

     			if (count($line_conflict) === 2)
                {
     				$file = explode('in', $line_conflict[1]);
     				array_shift($file);
     				$file = implode('in', $file);
     				$conflicts[] =
                    [
     					'type'  => str_replace([')', '(', 'CONFLICT '], '', $line_conflict[0]),
     					'file'  => $file,
     				];
     			}
     		}
     	}

        if (empty($conflicts))
        {
            return true;
        }

     	return $conflicts;
    }
}
