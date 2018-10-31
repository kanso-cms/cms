<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

/**
 * CMS Query cache access methods.
 *
 * @author Joe J. Howard
 */
trait CacheAccess
{
    /**
     * Get/set a post by id from the PostManager or cache.
     *
     * @access private
     * @param  int                           $post_id Post id
     * @return \kanso\cms\wrappers\Post|null
     */
    private function getPostByID(int $post_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->PostManager->byId($post_id));
    }

    /**
     * Get/set an author by id from the UserManager or cache.
     *
     * @access private
     * @param  int                           $author_id Author id
     * @return \kanso\cms\wrappers\User|null
     */
    private function getAuthorById(int $author_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->UserManager->provider()->byId($author_id));
    }

    /**
     * Get/set a tag by id from the TagManager or cache.
     *
     * @access private
     * @param  int                          $tag_id Tag id
     * @return \kanso\cms\wrappers\Tag|null
     */
    private function getTagById(int $tag_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->TagManager->provider()->byId($tag_id));
    }

    /**
     * Get/set a category by id from the CategoryManager or cache.
     *
     * @access private
     * @param  int                               $category_id Category id
     * @return \kanso\cms\wrappers\Category|null
     */
    private function getCategoryById(int $category_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->CategoryManager->provider()->byId($category_id));
    }

    /**
     * Get/set a media attachment by id from the MediaManager or cache.
     *
     * @access private
     * @param  int                            $thumb_id Media attachment id
     * @return \kanso\cms\wrappers\Media|null
     */
    private function getMediaById(int $thumb_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->MediaManager->provider()->byId($thumb_id));
    }
}
