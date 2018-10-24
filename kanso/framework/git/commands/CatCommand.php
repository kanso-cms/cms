<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands;

use kanso\framework\git\Command;

/**
 * Git cat-file command.
 *
 * @see  https://git-scm.com/docs/git-cat-file
 * @author Joe J. Howard
 */
class CatCommand extends Command
{
    /**
     * Magic method invoke.
     *
     * @param  array              $options Command options (optional) (default [])
     * @param  array              $params  Command params  (optional) (default [])
     * @return array|string|false
     */
    public function __invoke(array $options = [], array $params = [])
    {
        $output = $this->run('cat-file', [$options, $params]);

        if (!$this->is_successful() || empty($output))
        {
            return false;
        }

        if (isset($options['p']) || in_array('p', $options))
        {
            $data        = array_map('trim', explode("\n", $output));
            $type        = explode(' ', $data[0]);
            $committer   = explode('<', trim(str_replace('committer', '', $data[3])));
            $description = !empty(trim($data[6])) ? implode("\n", array_slice($data, 6)) : '';
            return [
                'type'   => $type[0],
                'hash'   => $type[1],
                'parent' => trim(str_replace('parent', '', $data[1])),
                'name'   => trim($committer[0]),
                'email'  => trim(explode('>', $committer[1])[0]),
                'date'   => intval(explode(' ', trim(explode('>', $committer[1])[1]))[0]),
                'title'  => $data[5],
                'description' => $description,
            ];
        }

        return $output;
    }
}
