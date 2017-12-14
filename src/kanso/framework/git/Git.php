<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git; 

use kanso\framework\shell\Shell;
use kanso\framework\git\commands\InitCommand;
use InvalidArgumentException;
use Exception;

/**
 * Git Utility class
 *
 * @author Joe J. Howard
 */
class Git
{
    /**
     * Available git commands
     *
     * @var array
     */
    private $commands =
    [
        'add'      => null,
        'archive'  => null,
        'branch'   => null,
        'cat'      => null,
        'checkout' => null,
        'clone'    => null,
        'commit'   => null,
        'config'   => null,
        'describe' => null,
        'diff'     => null,
        'fetch'    => null,
        'init'     => null,
        'log'      => null,
        'merge'    => null,
        'mv'       => null,
        'pull'     => null,
        'push'     => null,
        'rebase'   => null,
        'reset'    => null,
        'reflog'   => null,
        'revlist'  => null,
        'rm'       => null,
        'shortlog' => null,
        'show'     => null,
        'stage'    => null,
        'stash'    => null,
        'status'   => null,
        'tag'      => null,
        'tree'     => null,
        'tag'      => null,
    ];
    
    /**
     * Path to repo directory
     *
     * @var string
     */
    public $directory;

    /**
     * Shell utility
     *
     * @var string
     */
    public $shell;

    /**
     * Constructor
     *
     * @access public
     * @param string  $directory Directory path (optional) (default null)
     */
    public function __construct(string $directory = null)
    {
        if ($directory)
        {
            $this->setDirectory($directory);
        }
    }

    /**
     * Open an existing repository
     *
     * @access public
     * @param  string  $directory Repo directory path
     */
    public function open(string $directory)
    {
        $this->setDirectory($directory);
    }

    /**
     * Get the working directory
     *
     * @access public
     * @return string|null
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set the working directory
     *
     * @access public
     * @param  string $directory Directory path
     */
    public function setDirectory(string $directory)
    {
        $this->directory = $directory;

        # Create a new shell wrapper
        $this->shell = new Shell($directory);
    }
    
    /**
     * Initialize a repository and set the working directory
     *
     * @access public
     * @param  string  $directory Directory path
     * @param  array   $options   Options for repo init
     */
    public function init(string $directory, array $options = [])
    {
        # Set the local directory
        $this->setDirectory($directory);

        # Create the init command
        $init = new InitCommand;

        # Invoke
        $init($directory, $options);
    }

    /**
     * Magic methods for git commands  e.g $command = $git->CommandName;
     *
     * @access public
     * @param  string  $name git command name in lower case
     * @return mixed 
     * @throws InvalidArgumentException if command does not exist
     */
    public function __get($name)
    {
        $className = ucfirst($name).'Command';

        if (array_key_exists($name, $this->commands))
        {
            if (is_null($this->commands[$name]))
            {
                $class = __NAMESPACE__ . '\\command\\' . $className;
                
                $this->commands[$name] = new $class($this);
                
                return $this->commands[$name];
            }
            else
            {
                return $this->commands[$name];
            }
        }

        throw new InvalidArgumentException('Call to undefined command "'. __NAMESPACE__ . '\\command\\' . $className .'"');
    }

    /**
     * Calls sub-commands e.g $git->CommandName();
     *
     * @access public
     * @param  string $name      The name of a property
     * @param  array  $arguments An array of arguments
     * @throws \InvalidArgumentException if command does not exist
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $className = ucfirst($name).'Command';

        if (array_key_exists($name, $this->commands))
        {
            if (is_null($this->commands[$name]))
            {
                $class = __NAMESPACE__ . '\\commands\\' . $className;
                
                $this->commands[$name] = new $class($this);
            }

            return call_user_func_array($this->commands[$name], $arguments);
        }

        throw new InvalidArgumentException('Call to undefined command "'. __NAMESPACE__ . '\\commands\\' . $className.'"');
    }

    /**
     * Execute the current git command
     *
     * @access public
     * @param  string $name      The name of the git command to run
     * @param  array  $args      An array of arguments to pass to command
     * @return mixed
     * @throws Exception if directory is not set
     */
    public function execute($method, $args = []) 
    {
        if (!$this->directory)
        {
            throw new Exception('Cannot run git without setting a directory.');
        }

        # Options
        $options = isset($args[0]) ? $args[0] : [];

        # Params
        $params = isset($args[1]) ? $args[1] : [];

        # Make sure the method is lowercase
        $method = strtolower($method);
        
        # Set the method to call
        $this->shell->cmd('git', $method);

        # Add the options
        $this->shell->options($options);
        
        # Add the params
        $this->shell->params($params);

        # Run the command
        $result = $this->shell->run();

        # Reset the shell args
        $this->shell->reset(false);

        return $result;
    }

    /**
     * Was the last executed command successful ?
     *
     * @access public
     * @return bool  
     */
    public function is_successful(): bool
    {
        return $this->shell->is_successful();
    }
}
