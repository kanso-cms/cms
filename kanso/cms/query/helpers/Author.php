<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\utility\Str;

/**
 * CMS Query author methods.
 *
 * @author Joe J. Howard
 */
class Author extends Helper
{
    /**
     * Get the author of the current post or a post by id.
     *
     * @access public
     * @param  int|null                      $post_id Post id (optional) (Default NULL)
     * @return \kanso\cms\wrappers\User|null
     */
    public function the_author(int $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->parent->helper('cache')->getPostByID($post_id);

            if ($post)
            {
                return $this->parent->helper('cache')->getAuthorById($post->author_id);
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);
        }

        return null;
    }

    /**
     * Checks whether a given author exists by name or id.
     *
     * @access  public
     * @param  string|int $usernameOrId Username or id
     * @return bool
     */
    public function author_exists($usernameOrId): bool
    {
        $index = is_numeric($usernameOrId) ? 'id' : 'username';

        $usernameOrId = is_numeric($usernameOrId) ? intval($usernameOrId) : $usernameOrId;

        $author = $this->container->get('UserManager')->provider()->byKey($index, $usernameOrId, true);

        if ($author)
        {
            return $author->role === 'administrator' || $author->role === 'writer';
        }

        return false;
    }

    /**
     * Does the author of the current post or an author by id have a thumbnail attachment.
     *
     * @access  public
     * @param  int|null $author_id Author id or null for author of current post (optional) (Default NULL)
     * @return bool
     */
    public function has_author_thumbnail($author_id = null): bool
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return !empty($this->parent->helper('cache')->getMediaById($author->thumbnail_id));
            }

            return false;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {
                return !empty($this->parent->helper('cache')->getMediaById($author->thumbnail_id));
            }
        }

        return false;
    }

    /**
     * Get the author name of the current post or an author by id.
     *
     * @access  public
     * @param  int|null    $author_id Author id or null for author of current post (optional) (default NULL)
     * @return string|null
     */
    public function the_author_name(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return $author->name;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {
                return $author->name;
            }
        }

        return null;
    }

    /**
     * Get the author's full URL of the current post or an author by id.
     *
     * @access  public
     * @param  int|null    $author_id Author id or null for author of current post (optional) (default NULL)
     * @return string|null
     */
    public function the_author_url(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                $prefix = !empty($this->parent->blog_location()) ? '/' . $this->parent->blog_location() . '/' : '/';

                return $this->container->get('Request')->environment()->HTTP_HOST . $prefix . 'author/' . $author->slug;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {
                $prefix = !empty($this->parent->blog_location()) ? '/' . $this->parent->blog_location() . '/' : '/';

                return $this->container->get('Request')->environment()->HTTP_HOST . $prefix . 'author/' . $author->slug;
            }
        }

        return null;
    }

    /**
     * Get the authors thumbnail attachment of the current post or an author by id.
     *
     * @access  public
     * @param  int|null                       $author_id Author id or null for author of current post (optional) (default NULL)
     * @return \kanso\cms\wrappers\Media|null
     */
    public function the_author_thumbnail(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return $this->parent->helper('cache')->getMediaById($author->thumbnail_id);
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {

                return $this->parent->helper('cache')->getMediaById($author->thumbnail_id);
            }

            return null;
        }

        return null;
    }

    /**
     * Get the authors bio of the current post or an author by id.
     *
     * @access  public
     * @param  int|null    $author_id Author id or null for author of current post (optional) (default NULL)
     * @return string|null
     */
    public function the_author_bio(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return Str::nl2br($author->description);
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {
                return Str::nl2br($author->description);
            }

        }

        return null;
    }

    /**
     * Get the authors twitter URL of the current post or an author by id.
     *
     * @access  public
     * @param  int|null    $author_id Author id or null for author of current post (optional) (default NULL)
     * @return string|null
     */
    public function the_author_twitter(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return $author->twitter;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {
                return $author->twitter;
            }

        }

        return null;
    }

    /**
     * Get the authors google URL of the current post or an author by id.
     *
     * @access  public
     * @param  int|null    $author_id Author id or null for author of current post (optional) (default NULL)
     * @return string|null
     */
    public function the_author_google(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return $author->gplus;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {
                return $author->gplus;
            }

        }

        return null;
    }

    /**
     * Get the authors facebook URL of the current post or an author by id.
     *
     * @access  public
     * @param  int|null    $author_id Author id or null for author of current post (optional) (default NULL)
     * @return string|null
     */
    public function the_author_facebook(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return $author->facebook;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);

            if ($author)
            {
                return $author->facebook;
            }
        }

        return null;
    }

    /**
     * Get the authors instagram URL of the current post or an author by id.
     *
     * @access  public
     * @param  int|null    $author_id Author id or null for author of current post (optional) (default NULL)
     * @return string|null
     */
    public function the_author_instagram(int $author_id = null)
    {
        if ($author_id)
        {
            $author = $this->parent->helper('cache')->getAuthorById($author_id);

            if ($author)
            {
                return $author->instagram;
            }

            return null;
        }

        if (!empty($this->parent->post))
        {
            $author = $this->parent->helper('cache')->getAuthorById($this->parent->post->author_id);
            if ($author)
            {
                return $author->instagram;
            }
        }

        return null;
    }

    /**
     * Get an array of user object of all authors.
     *
     * @access public
     * @return array
     */
    public function all_the_authors(): array
    {
        $key = $this->parent->helper('cache')->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->parent->helper('cache')->has($key))
        {
            return $this->parent->helper('cache')->get($key);
        }

        $authors = [];

        $rows = $this->sql()->SELECT('id, role')->FROM('users')->FIND_ALL();

        foreach ($rows as $row)
        {
            if ($row['role'] !== 'administrator' && $row['role'] !== 'writer')
            {
                continue;
            }

            $authors[] = $this->container->get('UserManager')->byId($row['id']);
        }

        return $this->parent->helper('cache')->set($key, $authors);
    }

    /**
     * Ge an array of Post objects objects by author id.
     *
     * @access public
     * @param  int   $author_id The author id
     * @param  bool  $published Get only published articles (optional) (Default TRUE)
     * @return array
     */
    public function the_author_posts(int $author_id, bool $published = true): array
    {
        $key = $this->parent->helper('cache')->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->parent->helper('cache')->has($key))
        {
            return $this->parent->helper('cache')->get($key);
        }

        if ($this->parent->author_exists($author_id))
        {
            return $this->parent->helper('cache')->set($key, $this->container->get('PostManager')->provider()->byKey('posts.author_id', $author_id, false, $published));
        }

        return $this->parent->helper('cache')->set($key, []);
    }
}
