<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands;

use kanso\framework\git\Command;

/**
 * Git ls-tree command.
 *
 * @see  https://git-scm.com/docs/git-ls-tree
 * @author Joe J. Howard
 */
class TreeCommand extends Command
{
    /**
     * Magic method invoke.
     *
     * @param  array      $options   Command options (optional) (default [])
     * @param  array      $params    Command params  (optional) (default [])
     * @param  bool       $recursive Recursively iterate (optional) (default false)
     * @param  bool       $asRaw     Return raw output   (optional) (default false)
     * @return bool|array
     */
    public function __invoke($options = [], $params = [], bool $recursive = false, bool $asRaw = false)
    {
        // Default options
        $defaults = false;
        if (empty($options))
        {
            $defaults = true;
            $options  = ['full-name', 'full-tree', 'long'];
        }

        // Is this recursive
        if ($recursive)
        {
            $options[] = 'r';
        }

        // Run the command
        $output = $this->run('ls-tree', [$options, $params]);

        // Validate the output
        if (!$this->is_successful() || empty(trim($output)))
        {
            return false;
        }

        // Return raw output
        if ($asRaw || !$defaults)
        {
            return $output;
        }

        // Default tree objects
        $objects = [];

        // Process the output
        $lines  = preg_split('/\r?\n/', rtrim($output), -1, PREG_SPLIT_NO_EMPTY);

        // Object types
        $types = [
            'submodule' => 0,
            'tree'      => 1,
            'blob'      => 2,
        ];

        foreach ($lines as $line)
        {
            $meta = array_filter(array_map('trim', explode("\t", $line)));
            $file = array_pop($meta);
            $meta = array_values(array_filter(explode(' ', array_shift($meta))));

            $filePath = explode('/', $file);

            if (!isset($meta[3])) {
                $meta[3] = 0;
            }

            $objects[] =
            [
                'sort'      => sprintf('%d:%s', $types[$meta[1]], $file),
                'mode'      => $meta[0],
                'type'      => $meta[1],
                'hash'      => $meta[2],
                'path'      => $file,
                'size'      => intval($meta[3]),
                'file'      => $filePath[count($filePath)-1],
            ];
        }

        usort($objects, function($a, $b)
        {
            return strnatcasecmp($a['sort'], $b['sort']);
        });

        return $objects;

    }
}
