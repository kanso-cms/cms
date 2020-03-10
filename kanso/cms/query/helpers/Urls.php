<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

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
     * @return string
     */
    public function themes_directory(): string
    {
        return $this->container->Config->get('cms.themes_path');
    }

    /**
     * Get the path to the theme directory that holds all the theme folders.
     *
     * @return string
     */
    public function theme_name(): string
    {
        return $this->container->Config->get('cms.theme_name');
    }

    /**
     * Get the path to the theme directory that holds the currently active theme.
     *
     * @return string
     */
    public function theme_directory(): string
    {
        return $this->parent->themes_directory() . '/' . $this->parent->theme_name();
    }

    /**
     * Get the URL to the theme directory that holds the currently active theme.
     *
     * @return string
     */
    public function theme_url(): string
    {
        return str_replace($this->container->Request->environment()->DOCUMENT_ROOT, $this->container->Request->environment()->HTTP_HOST, $this->parent->theme_directory());
    }

    /**
     * Get the homepage URL.
     *
     * @return string
     */
    public function home_url(): string
    {
        return $this->container->Request->environment()->HTTP_HOST;
    }

    /**
     * Get the homepage URL for the blog.
     *
     * @return string
     */
    public function blog_url(): string
    {
        return !empty($this->parent->blog_location()) ? $this->container->Request->environment()->HTTP_HOST . '/' . $this->parent->blog_location() . '/' : $this->container->Request->environment()->HTTP_HOST;
    }

    /**
     * Returns the "blog_location" value.
     *
     * @return string|null
     */
    public function blog_location()
    {
        return $this->container->Config->get('cms.blog_location');
    }

    /**
     * Returns the configured attachments upload directory.
     *
     * @return string
     */
    public function the_attachments_url(): string
    {
        return str_replace($this->container->Request->environment()->DOCUMENT_ROOT, $this->container->Request->environment()->HTTP_HOST, $this->container->Config->get('cms.uploads.path'));
    }

    /**
     * Returns the base url.
     *
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
            $base = 'search-results' . DIRECTORY_SEPARATOR;
        }
        elseif ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author())
        {
            $taxonomy = $this->parent->is_tag() ? 'tag' : 'author';
            $taxonomy = $this->parent->is_category() ? 'category' : $taxonomy;
            $base     = $base . DIRECTORY_SEPARATOR . $taxonomy . DIRECTORY_SEPARATOR . $this->parent->taxonomySlug;
        }

        return $this->container->Request->environment()->HTTP_HOST . DIRECTORY_SEPARATOR . $base;
    }
}
