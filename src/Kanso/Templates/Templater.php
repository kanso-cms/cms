<?php

namespace Kanso\Templates;

/**
 * Templater
 *
 * This class should not be confused with any sort of Kanso theme 
 * templating. It is used internally to load dynamic HTML content
 * e.g sending HTML emails, or displaying comments when long strings
 * of HTML will just bloat classes
 *
 */
class Templater
{

	/**
     * Load a template with variables and return a string
     *
     * @param  $data        mixed    Variables to load into the template
     * @param  $template    string   Name of the template file to load
     * @return string
     */
	public static function getTemplate($template, $data = []) 
	{
		$templateFile = __DIR__.DIRECTORY_SEPARATOR.$template.'.php';
		if (file_exists($templateFile) && is_file($templateFile)) {
        	ob_start();
            extract($data);
        	require_once $templateFile;
        	return ob_get_clean();
		}
		return '';
	}
}