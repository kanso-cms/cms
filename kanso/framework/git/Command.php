<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git;

/**
 * Base class for git commands.
 *
 * @author Joe J. Howard
 */
abstract class Command
{
    /**
     * Git instance.
     *
     * @var kanso\framework\git\Git
     */
    protected $git;

    /**
     * Constructor.
     *
     * @param kanso\framework\git\Git $git Git Instance
     */
    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    /**
     * Run this git command.
     *
     * @param  string $name The name of the git command to run
     * @param  array  $args An array of arguments to pass to command (optional) (default [])
     * @return mixed
     */
    public function run($method, $args = [])
    {
        return $this->git->execute($method, $args);
    }

    /**
     * Was the last executed command successful ?
     *
     * @access public
     * @return bool
     */
    public function is_successful(): bool
    {
        return $this->git->is_successful();
    }
}
