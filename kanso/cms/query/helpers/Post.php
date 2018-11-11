<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\utility\Markdown;
use kanso\cms\query\helpers\Helper;

/**
 * CMS Query post methods.
 *
 * @author Joe J. Howard
 */
class Post extends Helper
{
    /**
     * Get the current post id.
     *
     * @access public
     * @return int|false
     */
    public function the_post_id()
    {
        if (!empty($this->parent->post))
        {
            return $this->parent->post->id;
        }

        return null;
    }

    /**
     * Get the excerpt of the current post or a post by id.
     *
     * @access  public
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_excerpt(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return $post->excerpt;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->post->excerpt;
        }

        return null;
    }

    /**
     * Get the status of the current post or post by id.
     *
     * @access  public
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_post_status(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return $post->status;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->post->status;
        }

        return null;
    }

    /**
     * Get the type of the current post or post by id.
     *
     * @access  public
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_post_type(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return $post->type;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->post->type;
        }

        return null;
    }

    /**
     * Get the meta for the current post or post by id.
     *
     * @access  public
     * @param  int   $post_id Post id or null for current post (optional) (Default NULL)
     * @return array
     */
    public function the_post_meta($post_id = null): array
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return $post->meta;
            }

            return [];
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->post->meta;
        }

        return [];
    }

    /**
     * Get the created time of the current post or a post by id.
     *
     * @access public
     * @param  string      $format  PHP date() string format (optional) (Default 'U')
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_time(string $format = 'U', int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return date($format, $post->created);
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return date($format, $this->parent->post->created);
        }

        return null;
    }

    /**
     * Get the last modified time of the current post or a post by id.
     *
     * @access public
     * @param  string      $format  PHP date() string format (optional) (Default 'U')
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_modified_time(string $format = 'U', int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return date($format, $post->modified);
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return date($format, $this->parent->post->modified);
        }

        return null;
    }

    /**
     * Does the current post or a post by id have a thumbnail attachment.
     *
     * @access public
     * @param  int  $post_id Post id or null for current post (optional) (Default NULL)
     * @return bool
     */
    public function has_post_thumbnail(int $post_id = null): bool
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post && !empty($post->thumbnail_id))
            {
                return !empty($this->parent->helpers['cache']->getMediaById($post->thumbnail_id));
            }

            return false;
        }

        if (!empty($this->parent->post) && !empty($this->parent->post->thumbnail_id))
        {
            return !empty($this->parent->helpers['cache']->getMediaById($this->parent->post->thumbnail_id));
        }

        return false;
    }

    /**
     * Get the title of the current post or a post by id.
     *
     * @access public
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_title(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return $post->title;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->post->title;
        }

        if (is_category() || is_tag() || is_author())
        {
            return the_taxonomy()->name;
        }

        return null;
    }

    /**
     * Get the full URL of the current post or a post by id.
     *
     * @access public
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_permalink(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                $prefix = !empty($this->parent->blog_location()) && $post->type === 'post' ? '/' . $this->parent->blog_location() . '/' : '/';

                return $this->container->get('Request')->environment()->HTTP_HOST . $prefix . trim($post->slug, '/') . '/';
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $prefix = !empty($this->parent->blog_location()) && $this->parent->post->type === 'post' ? '/' . $this->parent->blog_location() . '/' : '/';

            return $this->container->get('Request')->environment()->HTTP_HOST . $prefix . trim($this->parent->post->slug, '/') . '/';
        }

        return null;
    }

    /**
     * Get the slug of the current post or a post by id.
     *
     * @access public
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @return string|null
     */
    public function the_slug(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return trim($post->slug, '/') . '/';
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return trim($this->parent->post->slug, '/') . '/';
        }

        return null;
    }

    /**
     * Gets the HTML content for current post or a post by id.
     *
     * @access public
     * @param  int    $post_id Post id or null for current post (optional) (Default NULL)
     * @param  bool   $raw     Return raw content not HTML formatted (optional) (default false)
     * @return string
     */
    public function the_content(int $post_id = null, $raw = false): string
    {
        $content = '';

        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                $content = $post->content;
            }
        }
        else
        {
            if (!empty($this->parent->post))
            {
                $content = $this->parent->post->content;
            }
        }

        if (empty($content))
        {
            return '';
        }

        if ($raw)
        {
            return trim($content);
        }

        return Markdown::convert(trim($content));
    }

    /**
     * Gets an attachment object for the current post or a post by id.
     *
     * @access public
     * @param  int                            $post_id Post id or null for current post (optional) (Default NULL)
     * @return \kanso\cms\wrappers\Media|null
     */
    public function the_post_thumbnail(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helpers['cache']->getPostByID($post_id);

            if ($post)
            {
                return $this->parent->helpers['cache']->getMediaById($post->thumbnail_id);
            }

            return null;
        }
        elseif (!empty($this->parent->post))
        {
            return $this->parent->helpers['cache']->getMediaById($this->parent->post->thumbnail_id);
        }

        return null;
    }

    /**
     * Gets the thumbnail src for the current post or a post by id in a given size.
     *
     * @access public
     * @param  int         $post_id Post id or null for current post (optional) (Default NULL)
     * @param  string      $size    The post thumbnail size "small"|"medium"|"large"|"original" (optional) (Default 'original')
     * @return string|null
     */
    public function the_post_thumbnail_src(int $post_id = null, string $size = 'original')
    {
        $thumbnail = $this->parent->the_post_thumbnail($post_id);

        if ($thumbnail)
        {
            return $thumbnail->imgSize($size);
        }

        return null;
    }

    /**
     * Prints an HTML img tag from Kanso attachment object.
     *
     * @param  \kanso\cms\wrappers\Media $thumbnail The attachment to print
     * @param  string                    $size      The post thumbnail size "small"|"medium"|"large"|"original" (optional) (Default 'original')
     * @param  int                       $width     The img tag's width attribute  (optional) (Default '')
     * @param  int                       $height    The img tag's height attribute (optional) (Default '')
     * @param  string                    $classes   The img tag's class attribute  (optional) (Default '')
     * @param  string                    $id        The img tag's id attribute (optional) (Default '')
     * @return string
     */
    public function display_thumbnail($thumbnail, $size = 'original', $width = '', $height = '', string $classes = '', string $id = ''): string
    {
        $width    = !$width ? '' : 'width="' . intval($width) . '"';
        $height   = !$height ? '' : 'height="' . intval($height) . '"';
        $classes  = !$classes ? '' : 'class="' . $classes . '"';
        $id       = !$id ? '' : 'id="' . $id . '"';

        if (!$thumbnail)
        {
            return '<img src="_" ' . $width . ' ' . $height . ' ' . $classes . ' ' . $id . ' alt="" title="">';
        }

        $src = $thumbnail->imgSize($size);

        return '<img src="' . $src . '" ' . $width . ' ' . $height . ' ' . $classes . ' ' . $id . ' alt="' . $thumbnail->alt . '" title="' . $thumbnail->title . '" >';
    }

    /**
     * Get an array of POST objects of all static pages.
     *
     * @access public
     * @param  bool  $published Return only published posts (optional) (default true)
     * @return array
     */
    public function all_static_pages(bool $published = true): array
    {
        return $this->container->get('PostManager')->provider()->byKey('posts.type', 'page', false, $published);
    }

    /**
     * Get an array of POST objects of custom post types by type.
     *
     * @access public
     * @param  bool  $published Return only published posts (optional) (default true)
     * @return array
     */
    public function all_custom_posts(string $type, bool $published = true): array
    {
        return $this->container->get('PostManager')->provider()->byKey('posts.type', $type, false, $published);
    }
}
