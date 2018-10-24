<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands;

use kanso\framework\git\Command;
use kanso\framework\shell\Shell;

/**
 * Git clone command.
 *
 * @see  https://git-scm.com/docs/git-clone
 * @author Joe J. Howard
 */
class CloneCommand extends Command
{
    /**
     * Magic method invoke.
     *
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return bool
     */
    public function __invoke(array $options = [], array $params = []): bool
    {
        $shell = new Shell();

        $clone = $shell->cmd('git', 'clone')->options($options)->params($params)->run();

        return $shell->is_successful();
    }
}
