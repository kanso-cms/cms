<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\View;

/**
 * View class
 *
 * @author Joe J. Howard
 */
Class View 
{
    /**
     * Assoc array of extra template includes
     *
     * @var array
     */
    private $templates = [];

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * Adds a template to include when displaying
     *
     * @param  string $template  Absolute path to template file
     */
    public function template(string $template)
    {
        $this->templates[] = $template;
    }

    /**
     * Return the output from a template
     *
     * @param  string $template  Absolute path to template file
     * @param  array  $vars      Assoc array of variables (optional) (default [])
     * @return string 
     */
    public function display(string $template, array $vars = []): string
    {
        return $this->sandbox($template, $vars);
    }

    /**
     * Sandbox and output a template
     *
     * @param  string $template  Absolute path to template file
     * @param  array  $vars      Assoc array of variables (optional) (default [])
     * @return string 
     */
    private function sandbox(string $file, array $vars): string
    {
        $kanso = \Kanso\Kanso::instance();

        foreach ($this->templates as $template)
        {
            if (file_exists($template))
            {
                require_once $template;
            }
        }
        
        extract($vars);
        
        ob_start();
        
        require $file;
        
        return ob_get_clean();
    }
}
