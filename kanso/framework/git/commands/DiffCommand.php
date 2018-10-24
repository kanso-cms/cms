<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands;

use kanso\framework\git\Command;
use kanso\framework\git\Command\utility\DiffParser;

/**
 * Git diff command.
 *
 * @see  https://git-scm.com/docs/git-diff
 * @author Joe J. Howard
 */
class DiffCommand extends Command
{
    /**
     * Magic method invoke.
     *
     * @param  array       $options Command options (optional) (default [])
     * @param  array       $params  Command params  (optional) (default [])
     * @return false|array
     */
    public function __invoke($options = [], $params = [], $limitDiff = true)
    {
        // Resolve the options
        $options = array_merge(['minimal', 'no-color', 'no-ext-diff', 'M', 'dst-prefix=DST/', 'src-prefix=SRC/'], $options);

        // Run the command
        $output = $this->run('diff', [$options, $params]);

        if (!$this->is_successful())
        {
            return false;
        }

        return DiffParser::parse($output, $this->git, null, $limitDiff);
    }

    /**
     * Get a diff summary.
     *
     * @param  string      $left  Left branch
     * @param  string      $right Right branch
     * @param  string      $path  Directory path (optional) (default null)
     * @return false|array
     */
    public function summary(string $left, string $right = null, string $path = null)
    {
        $options = ['numstat'];
        $params  = [];

        if (is_null($right))
        {
            $params[] = $left . '^!';
        }
        else
        {
            $params[] = $left . '..' . $right;
        }

        if ($path)
        {
            $params[] = '--';
            $params[] = $path;
        }

        // Run the command
        $output = $this->run('diff', [$options, $params]);

        if (!$this->is_successful())
        {
            return false;
        }

        // Default return value
        $return =
        [
            'files'      => [],
            'additions'  => 0,
            'deletions'  => 0,
        ];

        $lines = array_map('trim', explode("\n", $output));

        foreach ($lines as $stat)
        {
            $data  = array_map('trim', explode("\t", $stat));
            if (empty($data) || empty($stat)) continue;
            $return['additions'] += intval($data[0]);
            $return['deletions'] += intval($data[1]);
            $return['files'][] = $data[2];
        }

        return $return;
    }
}
