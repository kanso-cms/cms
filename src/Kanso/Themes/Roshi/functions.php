<?php
/*
 * The functions.php file of any Kanso theme serves as an opportunity to 
 * customize your theme. Whenever a template from the active Theme is loaded, 
 * the functions.php file (if it exists) and anything inside it is made 
 * available to the theme template.
 */

/********************************************************************************
* CUSTOM EXCERPT
******************************************************************************/

function customExcerpt($length, $suffix = '', $toChar = true) {
    
    $excerpt = the_excerpt();

    if ($toChar) return (strlen($excerpt) > $length ) ? substr($excerpt, 0, $length).$suffix : $excerpt;

    $words = explode(' ', $excerpt);

    if(count($words) > $length) return implode(' ', array_slice($words, 0, $length)).$suffix;

    return $excerpt;
}