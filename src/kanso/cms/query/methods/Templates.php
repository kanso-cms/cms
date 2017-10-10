<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

use kanso\framework\utility\Str;

/**
 * CMS Query template methods
 *
 * @author Joe J. Howard
 */
trait Templates
{
    /**
     * Display the contents of header.php
     *
     * @access  public
     * @return string
     */
    public function the_header(): string
    {
        return $this->Response->view()->display($this->theme_directory().DIRECTORY_SEPARATOR.'header.php');
    }

    /**
     * Display the contents of footer.php
     *
     * @access public
     * @return string
     */
    public function the_footer(): string
    {
        return $this->Response->view()->display($this->theme_directory().DIRECTORY_SEPARATOR.'footer.php');
    }

    /**
     * Display the contents of sidebar.php
     *
     * @access public
     * @return string
     */
    public function the_sidebar(): string
    {
        return $this->Response->view()->display($this->theme_directory().DIRECTORY_SEPARATOR.'sidebar.php');
    }

    /**
     * Display the contents of any template file relative to the theme's base directory
     *
     * @access public
     * @param  string $template Template file name/path without .php extension
     * @param  array  $data     Array of variables to make available within file scope (optional) (default [])
     * @return string
     */
    public function include_template(string $template_name, array $data = []): string
    {
        $template = $this->theme_directory().DIRECTORY_SEPARATOR.$template_name.'.php';
        
        if (file_exists($template))
        {
            return $this->Response->view()->display($template, $data);
        }
        
        return '';
    }
}
