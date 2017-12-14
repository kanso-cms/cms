<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\git\commands; 

use kanso\framework\git\Command;
use kanso\framework\git\Command\utility\DiffParser;

/**
 * Git show command
 *
 * @see  https://git-scm.com/docs/git-show
 * @author Joe J. Howard
 */
class ShowCommand extends Command
{
    /**
     * Magic method invoke
     * 
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return false|string
     */
    public function __invoke(array $options = [], array $params = [])
    {
        # Run the command
        $output = $this->run('show', [$options, $params]);

        if (!$this->is_successful())
        {
            return false;
        }

        return $output;
    }

    /**
     * Show diffs
     * 
     * @param  array $options Command options (optional) (default [])
     * @param  array $params  Command params  (optional) (default [])
     * @return bool|array
     */
    public function diff($options = [], $params = [], $limitDiff = true) 
    {
    	$options = array_merge([ 'minimal', 'no-color', 'no-ext-diff', 'M', 'dst-prefix' => 'DST/', 'src-prefix'=> 'SRC/'], $options);

    	# Run the command
        $output = $this->run('show', [$options, $params]);

        if (!$this->is_successful())
        {
            return false;
        }

        return DiffParser::parse($output, $this->git, null, $limitDiff);
    }

    /**
     * Commit from blob
     * 
     * @param  string $branch Branch of blob
     * @param  string $hash   Blob hash
     * @param  string $path   Blob file path
     * @return bool|array
     */
    public function blobCommit(string $branch = 'HEAD', string $hash, string $path) 
    {
    	# Optiops and params
    	$options = [
    		'n' 	 => 1,
    		'format' => '%H||%P||%cN||%cE||%cD||%B',
    	];
    	$params = [ $branch, $hash, '--', $path];

    	# Run the command
        $output = $this->run('log', [$options, $params]);

        if (!$this->is_successful() || empty(trim($output)))
        {
            return false;
        }

        $lines  = array_map('trim', explode('||', $output));
        $body   = array_map('trim', explode("\n", $lines[5]));
        $title  = $body[0];
        array_shift($body);
        $description = !empty($body) ? trim(implode("\n", $body)) : '';
        
        return [
            'hash'   => $lines[0],
            'parent' => $lines[1],
            'name'   => $lines[2],
            'email'  => $lines[3],
            'date'   => $lines[4],
            'title'  => $title,
            'description' => $description,
        ];  
    }
}
