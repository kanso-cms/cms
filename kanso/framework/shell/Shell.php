<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\shell;

use kanso\framework\shell\process\Process;

/**
 * Shell interface utility class
 *
 * @author Joe J. Howard
 */
class Shell
{
    /**
     * Built in linux commands
     *
     * @var array
     */
    private $built_ins = ['alias','bg','bind','break','builtin','caller','cd','command','compgen','complete','continue','declare','dirs','disown','echo','enable','eval','exec','exit','export','false','fc','fg','getopts','hash','help','history','jobs','kill','let','local','logout','popd','printf','pushd','pwd','read','readonly','return','set','shift','shopt','source','suspend','test','times','trap','true','type','typeset','ulimit','umask','unalias','unset','wait'];

    /**
     * Flagged options to use
     *
     * @var array
     */
    private $options = [];

    /**
     * Quoted params to use
     *
     * @var array
     */
    private $params  = [];

    /**
     * Sub command to run
     *
     * @var string
     */
    private $subCmd  = null;

    /**
     * The actual cmd to run
     *
     * @var string
     */
    private $cmd = null;

    /**
     * The directory to run the cmd in
     *
     * @var string
     */
    private $dir = null;

    /**
     * Default timeout in seconds
     *
     * @var int
     */
    private $timeout = 10;

    /**
     * Smyfony process
     *
     * @var kanso\framework\shell\process\Process
     */
    private $process;

    /**
     * Input stream
     *
     * @var string
     */
    private $_I;

    /**
     * Output stream
     *
     * @var string
     */
    private $_O;

    /**
     * Result from the command
     *
     * @var string
     */
    private $result = false;

    /**
     * Default available system memory
     *
     * @var string
     */
    private $defaultMemory = '1024M';

    /**
     * Constructor
     *
     * @access public
     * @param  string  $directory Directory to run command on (optional) (default null)
     */
    public function __construct(string $dir = null) 
    {        
        $this->process = new Process(null);
        
        $this->process->setTimeout($this->timeout);
        
        if ($dir)
        {
            $this->cd($dir);
        }
    }

    /**
     * Cd into a directory
     *
     * @access public
     * @param  string $directory Directory to move to
     * @return this
     */
    public function cd(string $dir)
    {
        $this->dir = escapeshellarg($dir);
        
        return $this;
    }

    /**
     * Run a command and optional sub command
     *
     * @access public
     * @param  string $cmd    Command name
     * @param  string $subCmd sub command name (optional) (default null)
     * @return this
     */
    public function cmd(string $cmd, string $subCmd = null)
    {
        $this->cmd = escapeshellcmd($cmd);

        if ($subCmd)
        {
            $this->subCmd = $subCmd;
        }

        return $this;
    }

    /**
     * Add an option or flag to the current command
     *
     * @access public
     * @param  string $flag  Option key or flag   
     * @param  string $value Option value (optional) (default null)
     * @return this
     */
    public function option(string $flag, string $value = null)
    {
        # No supplied value
        if ($value === false)
        {
            return;
        }
        else if ($value === null)
        {
            $this->options[$flag] = htmlspecialchars($flag, ENT_QUOTES);
        }
        # Value is true we use the flat key
        else if ($value === true)
        {
            $this->options[$flag] = htmlspecialchars($flag, ENT_QUOTES);
        }
        # Flag is a key/value
        else
        {
            $this->options[$flag] = [htmlspecialchars($flag, ENT_QUOTES), escapeshellarg($value)];
        }
        
        return $this;
    }

    /**
     * Add an array of flags and options
     *
     * @access public
     * @param  array  $options Array of options with flags
     * @return this
     */
    public function options(array $options)
    {
        foreach ($options as $key => $flag)
        {
            if (is_numeric($key))
            {
                $this->option($flag);
            }
            else
            {
                $this->option($key, $flag);
            }
        }

        return $this;
    }

    /**
     * Add parameter to the current command
     *
     * @access public
     * @param  string  $param Parameter to add
     * @return this
     */
    public function param($param = null)
    {
        if (!$param)
        {
            return;
        }

        $this->params[$param] = escapeshellarg($param);

        return $this;
    }

    /**
     * Add an array of parameters to the current command
     *
     * @access public
     * @param  array  $params Array of parameters to add
     * @return this
     */
    public function params(array $params)
    {
        foreach ($params as $param)
        {
            $this->param($param);
        }
        return $this;
    }

    /**
     * Add an input argument to the command
     *
     * @access public
     * @param  string $path Path to command input
     * @return this
     */
    public function input(string $path)
    {
        $this->_I = escapeshellarg($path);

        return $this;
    }

    /**
     * Add an output argument to the command
     *
     * @access public
     * @param  string  $path Path to command output
     * @return this
     */
    public function output(string $path)
    {
        $this->_O = escapeshellarg($path);

        return $this;
    }

    /**
     * Run the command
     *
     * @access public
     * @param  bool  $showErrors Return errors or output
     * @return this
     */
    public function run($showErrors = false)
    {
        ini_set('memory_limit', '1024M');

        $cmd_str = '';
        
        # Add the directory
        if ($this->dir)
        {
            $cmd_str .= 'cd '.$this->dir.' && ';
        }
        
        # Add the command
        # Resolve any missing binaries
        $cmd_str .= $this->cmd.' ';

        # Add the subcmd
        if ($this->subCmd)
        {
            $cmd_str .= $this->subCmd.' ';
        }

        # Add the options
        foreach ($this->options as $key => $option)
        {
            
            # Is the option a key/val
            if (is_array($option))
            {

                # Single letter commands get a single "-" infront of them
                if (strlen($option[0]) === 1)
                {
                    $cmd_str .= '-'.$option[0].' '.$option[1].' ';
                }
                else
                {
                    $cmd_str .= '--'.$option[0].'='.$option[1].' ';
                }

            }
            else
            {
                # Single letter commands get a single "-" infront of them
                if (strlen($option) === 1)
                {
                    $cmd_str .= '-'.$option.' ';
                }
                else
                {
                    $cmd_str .= '--'.$option.' ';
                }
            }
        }

        # Add the params
        foreach ($this->params as $key => $param)
        {
            $cmd_str .= $param.' ';
        }

        # Add any I/O steams
        if ($this->_I) $cmd_str .= ' < '.$this->_I;
        if ($this->_O) $cmd_str .= ' > '.$this->_O;

        # Run the process
        $this->process->setCommandLine($cmd_str);
        $this->process->run();

        # Was the command successfull ?
        $this->result = $this->process->isSuccessful();

        $output = $this->process->getOutput();
        $errors = $this->process->getErrorOutput();

        # Do a soft reset - keeps the dir, result and output
        $this->reset();

        if (!$this->result && !$showErrors) return $errors;

        # Return the result
        return $output;
    }

    public function is_successful()
    {
        return $this->result !== false;
    }

    public function reset($hard_reset = false)
    {
        $this->options = [];
        $this->params  = [];
        $this->subCmd  = null;
        $this->cmd     = null;
        $this->_O      = null;
        $this->_I      = null;
        $this->process->clearOutput();
        $this->process->clearOutput();
        $this->process->setCommandLine(null);
        if ($hard_reset === true)
        {
            $this->dir     = null;
            $this->result  = false;
            $this->output  = [];
        }
    }

    # Makes sure than the binary for the cmd being run exists
    private function resolveBins($cmd)
    {   
        # If this is a built in command we can skip
        if (in_array($cmd, $this->built_ins)) return $cmd;
        
        # Get the env paths
        $paths = array_map('trim', explode(':', getenv('PATH')));

        # Loop the current env paths for the binary
        foreach ($paths as $path)
        {

            # The bin should exist
            $bin = rtrim($path, '/').'/'.$cmd;

            if (file_exists($bin)) return $bin;

        }

        # If that failed let's add "/usr/local/bin" to the evn paths
        if (!in_array('/usr/local/bin', $paths))
        {
            putenv('PATH=' . getenv('PATH') . ':/usr/local/bin');
            return '/usr/local/bin/'.$cmd;
        }

        # Just return the command otherwise
        return $cmd;
    }

}
