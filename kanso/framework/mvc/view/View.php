<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\view;

use kanso\Kanso;
use kanso\framework\mvc\view\ViewBase;
use kanso\framework\mvc\view\ViewInterface;

/**
 * Default view implementation
 *
 * @author Joe J. Howard
 */
class View extends ViewBase implements ViewInterface
{
    /**
	 * {@inheritdoc}
	 */
	public function include(string $file)
	{
		$this->includes[$file] = $file;
	}

	/**
	 * {@inheritdoc}
	 */
	public function includes(array $files)
	{
		foreach ($files as $path)
		{
			$this->include($path);
		}
	}

    /**
	 * {@inheritdoc}
	 */
	public function display(string $file, array $data = []): string
	{
		return $this->sandbox($file, $data);
	}

    /**
     * Sandbox and output a template
     *
     * @param  string $file Absolute path to template file
     * @param  array  $data Assoc array of variables (optional) (default [])
     * @return string 
     */
    private function sandbox(string $file, array $data): string
    {
        $kanso = Kanso::instance();

        foreach ($this->includes as $include)
        {
            if (file_exists($include))
            {
                require_once $include;
            }
        }
        
        extract($data);
        
        ob_start();
        
        require $file;
        
        return ob_get_clean();
    }
}
