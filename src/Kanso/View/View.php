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
    private $data;

    /**
     * @var Kanso\MVC\View
     */
    private static $instance;

    /**
     * Get View instance (singleton)
     *
     * This creates and/or returns an View instance (singleton)
     *          
     * @return Kanso\MVC\View
     */
    public static function getInstance() 
    {
        if (!isset(self::$instance)) {
            self::$instance = new View();
        }
        return self::$instance;
    }

    /**
     * Private Constructor
     */
    private function __construct()
    {
        $this->data  = [];
    }
    
    /**
     * Append data to be used on template
     * @param array $data
     */
    public function appendData($data) 
    {   
        if (!is_null($data)) $this->data = array_merge($this->data, $data);
    }

    /**
     * Create an output buffer from a template
     *
     * This function will include the functions from the Query
     * for use in templates
     *
     * @see \Kanso\Helper\ToolBox
     * @see \Kanso\MVC\ViewIncludes.php
     * @param string $template  Absolute path to template file
     */
    public function display($template) 
    {
        $Kanso     = \Kanso\Kanso::getInstance();
        $functions = $Kanso->Environment['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'functions.php';
        if (file_exists($functions)) require_once $functions;
        require_once 'ViewIncludes.php';
        extract($this->data);
        ob_start();
        require_once $template;
        return ob_get_clean();
    }

}