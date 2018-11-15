<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;

/**
 * CMS Query pagination methods.
 *
 * @author Joe J. Howard
 */
class Pagination extends Helper
{
    /**
     * Build the pagination links for the current page.
     * Works on home, search, tag, category, author requests.
     *
     * @access public
     * @param  array|null $args Array of display arguments (optional) (default NULL)
     * @return string
     */
    public function pagination_links(array $args = null): string
    {
        // Default options
        $options = [
          'base'               => $this->container->get('Request')->environment()->HTTP_HOST,
          'format'             => '<li class="(:class)"><a href="(:link)">(:num)</a></li>',
          'format_disabled'    => '<li class="(:class)"><span>(:num)</span></li>',
          'white_space'        => ' ',
          'current'            => 1,
          'total'              => 1,
          'context'            => 2,
          'show_all'           => false,
          'prev_next'          => true,
          'ellipsis'           => '<li>. . .</li>',
          'prev_text'          => '« Previous',
          'next_text'          => 'Next »',
        ];

        // Segment the reuest URI
        $uri = explode('/', Str::queryFilterUri($this->container->get('Request')->environment()->REQUEST_URI));

        // Declare the pagination string
        $pagination = '';

        // Count the posts
        $posts = $this->parent->helper('parser')->countQuery($this->parent->queryStr);
        $pages = Arr::paginate($posts, $this->parent->pageIndex, $this->container->get('Config')->get('cms.posts_per_page'));

        // If no args were defined, Kanso will figure it out for us
        if (!$args || !isset($args['current']) || !isset($args['total']))
        {
            // pages here are used as for an array so +1
            $options['current'] = $this->parent->pageIndex === 0 ? 1 : $this->parent->pageIndex+1;

            $options['total'] = is_array($pages) ? count($pages) : 1;
        }

        // If options were set, overwrite the dafaults
        if ($args)
        {
            $options = array_merge($options, $args);
        }

        // Special case if there is only 1 page
        if ($options['total'] == 1 || $options['total'] == 0 || $options['total'] < 1)
        {
            return '';
        }

        // Clean the base url
        $options['base'] = rtrim($options['base'], '/');

        // Update the base url depending on the page type
        $options['base'] = $this->parent->base_url();

        // loop always at the current minus the context, minus 1
        $loopStart  = ($options['current'] - $options['context']);

        // if the loop starts before 2, reset it to 2
        if ($loopStart < 2) $loopStart = 2;

        // Loop end is the context * 2 + loop start + plus 1
        $loopEnd    = $loopStart + ($options['context'] * 2) + 1;

        // We should show all links if the loop ends after the total
        if ($loopEnd >= $options['total'] || $options['show_all'] === true)
        {
            $loopEnd = $options['total'];
        }

        // Declare variables we are going to use
        $frontEllipsis = $loopStart > 2 ? $options['ellipsis'] :  '';
        $backEllipsis  = $loopEnd === $options['total'] || $options['total'] - $options['context'] === $loopEnd ? '' : $options['ellipsis'];

        // Variables we will need
        $patterns     = ['/\(:class\)/', '/\(:link\)/', '/\(:num\)/'];
        $replacements = [];

        // If show all is true we need reset
        if ($options['show_all'] === true)
        {
            $frontEllipsis = '';
            $backEllipsis  = '';
            $loopStart     = 2;
            $loopEnd       = $options['total'];
        }

        // If show previous
        if ($options['prev_next'] === true)
        {
            $class  = $options['current'] === 1  ? 'disabled' : '';
            $link   = $options['current'] === 1  ? '#' : $options['base'] . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR . ($options['current']-1) . DIRECTORY_SEPARATOR;
            $link   = $options['current'] === 2  ? $options['base'] : $link;
            $format = $options['current'] === 1  ? $options['format_disabled'] : $options['format'];
            $replacements = [$class, $link, $options['prev_text']];
            $pagination  .= preg_replace($patterns, $replacements, $format) . $options['white_space'];
            $replacements = [];
        }

        // Show the first page
        $class = $options['current'] === 1  ? 'active' : '';
        $link  = $options['current'] === 1  ? '#' : $options['base'] . DIRECTORY_SEPARATOR;
        $replacements = [$class, $link, 1];
        $pagination  .= preg_replace($patterns, $replacements, $options['format']) . $options['white_space'];
        $replacements = [];

        // Show the front ellipsis
        $pagination .= $frontEllipsis;

        // Loop over the pages
        // Note the loop starts after the first page and before the last page
        for ($i = $loopStart; $i < $loopEnd; $i++)
        {
            $class = $i === $options['current'] ? 'active' : '';
            $link  = $i === $options['current'] ? '#' : $options['base'] . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR . ($i) . DIRECTORY_SEPARATOR;
            $replacements = [$class, $link, $i];
            $pagination  .= preg_replace($patterns, $replacements, $options['format']) . $options['white_space'];
            $replacements = [];
        }

        // Show the back ellipsis
        $pagination .= $backEllipsis . $options['white_space'];

        // Show the last page
        $class = $options['current'] === $options['total'] ? 'active' : '';
        $link  = $options['current'] === $options['total'] ? '#' : $options['base'] . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR . $options['total'] . DIRECTORY_SEPARATOR;
        $replacements = [$class, $link, $options['total']];
        $pagination  .= preg_replace($patterns, $replacements, $options['format']) . $options['white_space'];
        $replacements = [];

        // If show next
        if ($options['prev_next'] === true)
        {
            $class  = $options['current'] <  $options['total'] ? '' : 'disabled';
            $format = $options['current'] <  $options['total'] ? $options['format'] : $options['format_disabled'];
            $link   = $options['current'] <  $options['total'] ? $options['base'] . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR . ($options['current']+1) . DIRECTORY_SEPARATOR : '#';
            $replacements = [$class, $link, $options['next_text']];
            $pagination  .= preg_replace($patterns, $replacements, $format) . $options['white_space'];
        }

        return $pagination;
    }
}
