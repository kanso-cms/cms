<?php

namespace Kanso\View;

/**
 * View
 *
 * This class is used to load templates into an output buffer and return 
 * the output. This is Kanso's main point for loading templates from theme files
 *
 * All front-end GET request response should be parsed through the view
 *
 * This is a singleton class; derived variables will
 * be common across multiple Kanso applications. 
 *
 */
Class View {

    /**
     * @var array Associative array of data
     */
    private $data = [];

    /**
     * @var array Associative array of templates to load
     */
    private $templates = [];

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Magic method overrides
     */
    public function __get($key)
    {  
        if (isset($this->data[$key])) return $this->data[$key];
        return NULL;
    }
    public function __set($key, $value)
    {  
        $this->data[$key] = $value;
    }
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }
    public function __unset($key)
    {
        if (isset($this->data[$key])) unset($this->data[$key]);
    }

    /**
     * Append data to be used on template
     *
     * @param array $data
     */
    public function setMultiple($data) 
    {   
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Append data to be used on template
     *
     * @param array $data
     */
    public function template($template) 
    {   
        $this->templates[] = $template;
    }

    /**
     * Create an output buffer from a template
     *
     * This function will include the functions from the Query
     * for use in templates
     *
     * @see \Kanso\MVC\ViewIncludes.php
     * @param string $template  Absolute path to template file
     * @param array  $vars      Assoc array of variables
     */
    public function display($template = null, $vars = null) 
    {
        if ($template) $this->template($template);
        if ($vars) $this->setMultiple($vars);
        $output = '';
        foreach ($this->templates as $file) {
            $output .= $this->sandbox($file);
        }
        return $output;
    }

    /**
     * Sandbox and output the content from the template
     */
    private function sandbox($file)
    {
        $Kanso     = \Kanso\Kanso::getInstance();
        $functions = $Kanso->Environment['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'functions.php';
        if (file_exists($functions)) require_once $functions;
        require_once 'ViewIncludes.php';
        extract($this->data);
        ob_start();
        require_once $file;
        return ob_get_clean();
    }

}