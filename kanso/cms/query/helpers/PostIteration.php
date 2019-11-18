<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

/**
 * CMS Query post iteration methods.
 *
 * @author Joe J. Howard
 */
class PostIteration extends Helper
{
    /**
     * Increment the internal pointer by 1 and return the current post
     * or just return a single post by id.
     *
     * @param  int|null                      $post_id Post id or null for next post in loop (optional) (Default NULL)
     * @return \kanso\cms\wrappers\Post|null
     */
    public function the_post(int $post_id = null)
    {
        if ($post_id)
        {
            return $this->parent->helper('cache')->getPostByID($post_id);
        }

        return $this->parent->_next();
    }

    /**
     * Get all the posts from the current query.
     *
     * @return array
     */
    public function the_posts(): array
    {
        return $this->parent->posts;
    }

    /**
     * Returns the post count of the current page of results for the current request.
     *
     * @return int
     */
    public function the_posts_count(): int
    {
        return $this->parent->postCount;
    }

    /**
     * Returns the posts per page value from the config.
     *
     * @return int
     */
    public function posts_per_page(): int
    {
        return $this->container->Config->get('cms.posts_per_page');
    }

    /**
     * Do we have posts in the loop? or does a post by id exist ?
     *
     * @param  int|null $post_id Post id or null for current loop (optional) (Default NULL)
     * @return bool
     */
    public function have_posts(int $post_id = null): bool
    {
        if ($post_id)
        {
            return !empty($this->parent->helper('cache')->getPostByID($post_id));
        }

        return $this->parent->postIndex < $this->parent->postCount -1;
    }

    /**
     * Rewind the internal pointer to the '-1'.
     */
    public function rewind_posts(): void
    {
        $this->parent->postIndex = -1;

        if ($this->parent->postCount > 0)
        {
            $this->parent->post = $this->parent->posts[0];
        }
    }

    /**
     * Iterate to the next post and return the post object if it exists.
     *
     * @return \kanso\cms\wrappers\Post|null
     */
    public function _next()
    {
        $this->parent->postIndex++;

        if (isset($this->parent->posts[$this->parent->postIndex]))
        {
            $this->parent->post = $this->parent->posts[$this->parent->postIndex];
        }
        else
        {
            $this->parent->post = null;
        }

        return $this->parent->post;
    }

    /**
     * Iterate to the previous post.
     *
     * @return \kanso\cms\wrappers\Post|null
     */
    public function _previous()
    {
        $this->parent->postIndex--;

        if (isset($this->parent->posts[$this->parent->postIndex]))
        {
            $this->parent->post = $this->parent->posts[$this->parent->postIndex];
        }
        else
        {
            $this->parent->post = null;
        }

        return $this->parent->post;
    }
}
