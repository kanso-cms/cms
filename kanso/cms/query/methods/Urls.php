<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

/**
 * CMS Query URL methods.
 *
 * @author Joe J. Howard
 */
trait Urls
{
    /**
     * Get the path to the theme directory that holds all the themes.
     *
     * @access public
     * @return string
     */
    public function themes_directory(): string
    {
        return $this->Config->get('cms.themes_path');
    }

    /**
     * Get the path to the theme directory that holds all the theme folders.
     *
     * @access public
     * @return string
     */
    public function theme_name(): string
    {
        return $this->Config->get('cms.theme_name');
    }

    /**
     * Get the path to the theme directory that holds the currently active theme.
     *
     * @access public
     * @return string
     */
    public function theme_directory(): string
    {
        return $this->themes_directory() . '/' . $this->theme_name();
    }

    /**
     * Get the URL to the theme directory that holds the currently active theme.
     *
     * @access public
     * @return string
     */
    public function theme_url(): string
    {
        return str_replace($this->Request->environment()->DOCUMENT_ROOT, $this->Request->environment()->HTTP_HOST, $this->theme_directory());
    }

    /**
     * Get the homepage URL.
     *
     * @access public
     * @return string
     */
    public function home_url(): string
    {
        return $this->Request->environment()->HTTP_HOST;
    }

    /**
     * Get the homepage URL for the blog.
     *
     * @access public
     * @return string
     */
    public function blog_url(): string
    {
        return !empty($this->blog_location()) ? $this->Request->environment()->HTTP_HOST . '/' . $this->blog_location() . '/' : $this->Request->environment()->HTTP_HOST;
    }

    /**
     * Returns the "blog_location" value.
     *
     * @access public
     * @return string|null
     */
    public function blog_location()
    {
        return $this->Config->get('cms.blog_location');
    }

    /**
     * Returns the configured attachments upload directory.
     *
     * @access public
     * @return string
     */
    public function the_attachments_url(): string
    {
        return str_replace($this->Request->environment()->DOCUMENT_ROOT, $this->Request->environment()->HTTP_HOST, $this->Config->get('cms.uploads.path'));
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

        if ($this->is_tag() || $this->is_category() || $this->is_author() || $this->is_blog_location())
        {
            $base = !empty($this->blog_location()) ? $this->blog_location() : '';
        }

        if ($this->is_search())
        {
            $base = DIRECTORY_SEPARATOR . 'search-results' . DIRECTORY_SEPARATOR . '?q=' . $this->searchQuery;
        }
        elseif ($this->is_tag() || $this->is_category() || $this->is_author())
        {
            $taxonomy = $this->is_tag() ? 'tag' : 'author';
            $taxonomy = $this->is_category() ? 'category' : $taxonomy;
            $base     = $base . DIRECTORY_SEPARATOR . $taxonomy . DIRECTORY_SEPARATOR . $this->taxonomySlug;
        }

        return $this->Request->environment()->HTTP_HOST . DIRECTORY_SEPARATOR . $base;
    }
}
