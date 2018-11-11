<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\cms\query\helpers\Helper;

/**
 * CMS Query URL methods.
 *
 * @author Joe J. Howard
 */
class Urls extends Helper
{
    /**
     * Get the path to the theme directory that holds all the themes.
     *
     * @access public
     * @return string
     */
    public function themes_directory(): string
    {
        return $this->container->get('Config')->get('cms.themes_path');
    }

    /**
     * Get the path to the theme directory that holds all the theme folders.
     *
     * @access public
     * @return string
     */
    public function theme_name(): string
    {
        return $this->container->get('Config')->get('cms.theme_name');
    }

    /**
     * Get the path to the theme directory that holds the currently active theme.
     *
     * @access public
     * @return string
     */
    public function theme_directory(): string
    {
        return $this->parent->themes_directory() . '/' . $this->parent->theme_name();
    }

    /**
     * Get the URL to the theme directory that holds the currently active theme.
     *
     * @access public
     * @return string
     */
    public function theme_url(): string
    {
        return str_replace($this->container->get('Request')->environment()->DOCUMENT_ROOT, $this->container->get('Request')->environment()->HTTP_HOST, $this->parent->theme_directory());
    }

    /**
     * Get the homepage URL.
     *
     * @access public
     * @return string
     */
    public function home_url(): string
    {
        return $this->container->get('Request')->environment()->HTTP_HOST;
    }

    /**
     * Get the homepage URL for the blog.
     *
     * @access public
     * @return string
     */
    public function blog_url(): string
    {
        return !empty($this->parent->blog_location()) ? $this->container->get('Request')->environment()->HTTP_HOST . '/' . $this->parent->blog_location() . '/' : $this->container->get('Request')->environment()->HTTP_HOST;
    }

    /**
     * Returns the "blog_location" value.
     *
     * @access public
     * @return string|null
     */
    public function blog_location()
    {
        return $this->container->get('Config')->get('cms.blog_location');
    }

    /**
     * Returns the configured attachments upload directory.
     *
     * @access public
     * @return string
     */
    public function the_attachments_url(): string
    {
        return str_replace($this->container->get('Request')->environment()->DOCUMENT_ROOT, $this->container->get('Request')->environment()->HTTP_HOST, $this->container->get('Config')->get('cms.uploads.path'));
    }

    /**
     * Returns the base url.
     *
     * @access public
     * @return string
     */
    public function base_url(): string
    {
        $base = '';

        if ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author() || $this->parent->is_blog_location())
        {
            $base = !empty($this->parent->blog_location()) ? $this->parent->blog_location() : '';
        }

        if ($this->parent->is_search())
        {
            $base = DIRECTORY_SEPARATOR . 'search-results' . DIRECTORY_SEPARATOR . '?q=' . $this->parent->searchQuery;
        }
        elseif ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author())
        {
            $taxonomy = $this->parent->is_tag() ? 'tag' : 'author';
            $taxonomy = $this->parent->is_category() ? 'category' : $taxonomy;
            $base     = $base . DIRECTORY_SEPARATOR . $taxonomy . DIRECTORY_SEPARATOR . $this->parent->taxonomySlug;
        }

        return $this->container->get('Request')->environment()->HTTP_HOST . DIRECTORY_SEPARATOR . $base;
    }
}
