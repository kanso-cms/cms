<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

/**
 * CMS Query post iteration methods
 *
 * @author Joe J. Howard
 */
trait PostIteration
{
    /**
     * Increment the internal pointer by 1 and return the current post 
     * or just return a single post by id
     *
     * @access public
     * @param  int    $post_id Post id or null for next post in loop (optional) (Default NULL)
     * @return \kanso\cms\wrappers\Post|null
     */
    public function the_post(int $post_id = null)
    {        
        if ($post_id)
        {
            return $this->getPostByID($post_id);
        }

        return $this->_next();
    }

    /**
     * Get all the posts from the current query
     *
     * @access public
     * @return array
     */
    public function the_posts(): array
    {
        return $this->posts;
    }

    /**
     * Returns the post count of the current page of results for the current request
     *
     * @access public
     * @return int
     */
    public function the_posts_count(): int
    {
        return $this->postCount;
    }

    /**
     * Returns the posts per page value from the config
     *
     * @access public
     * @return int
     */
    public function posts_per_page(): int
    {
        return $this->Config->get('cms.posts_per_page');
    }

    /**
     * Do we have posts in the loop? or does a post by id exist ?
     *
     * @access  public
     * @param   int    $post_id Post id or null for current loop (optional) (Default NULL)
     * @return  bool
     */
    public function have_posts(int $post_id = null): bool
    {
        if ($post_id)
        {
            return !empty($this->getPostByID($post_id));
        }

        return $this->postIndex < $this->postCount -1;
    }

    /**
     * Rewind the internal pointer to the '-1'
     *
     * @access public
     */
    public function rewind_posts()
    {
        $this->postIndex = -1;

        if ($this->postCount > 0 )
        {
            $this->post = $this->posts[0];
        }
    }

    /**
     * Iterate to the next post and return the post object if it exists
     *
     * @access public
     * @return \kanso\cms\wrappers\Post|null
     */
    public function _next()
    {
        $this->postIndex++;
        
        if (isset($this->posts[$this->postIndex]))
        {
            $this->post = $this->posts[$this->postIndex];
        }
        else
        {
            $this->post = null;
        }

        return $this->post;
    }

    /**
     * Iterate to the previous post
     *
     * @access public
     * @return \kanso\cms\wrappers\Post|null
     */
    public function _previous()
    {
        $this->postIndex--;

        if (isset($this->posts[$this->postIndex]))
        {
            $this->post = $this->posts[$this->postIndex];
        }
        else
        {
            $this->post = NULL;
        }

        return $this->post;
    }    
}
