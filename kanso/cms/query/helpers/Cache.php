<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\common\ArrayAccessTrait;

/**
 * Cache for query.
 *
 * @author Joe J. Howard
 */
class Cache extends Helper
{
	use ArrayAccessTrait;

    /**
     * Get/set a post by id from the PostManager or cache.
     *
     * @param  int                           $post_id Post id
     * @return \kanso\cms\wrappers\Post|null
     */
    public function getPostByID(int $post_id)
    {
        $key = $this->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->has($key))
        {
            return $this->get($key);
        }

        return $this->set($key, $this->container->PostManager->byId($post_id));
    }

    /**
     * Get/set an author by id from the UserManager or cache.
     *
     * @param  int                           $author_id Author id
     * @return \kanso\cms\wrappers\User|null
     */
    public function getAuthorById(int $author_id)
    {
        $key = $this->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->has($key))
        {
            return $this->get($key);
        }

        return $this->set($key, $this->container->UserManager->provider()->byId($author_id));
    }

    /**
     * Get/set a tag by id from the TagManager or cache.
     *
     * @param  int                          $tag_id Tag id
     * @return \kanso\cms\wrappers\Tag|null
     */
    public function getTagById(int $tag_id)
    {
        $key = $this->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->has($key))
        {
            return $this->get($key);
        }

        return $this->set($key, $this->container->TagManager->provider()->byId($tag_id));
    }

    /**
     * Get/set a category by id from the CategoryManager or cache.
     *
     * @param  int                               $category_id Category id
     * @return \kanso\cms\wrappers\Category|null
     */
    public function getCategoryById(int $category_id)
    {
        $key = $this->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->has($key))
        {
            return $this->get($key);
        }

        return $this->set($key, $this->container->CategoryManager->provider()->byId($category_id));
    }

    /**
     * Get/set a media attachment by id from the MediaManager or cache.
     *
     * @param  int                            $thumb_id Media attachment id
     * @return \kanso\cms\wrappers\Media|null
     */
    public function getMediaById(int $thumb_id)
    {
        $key = $this->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->has($key))
        {
            return $this->get($key);
        }

        return $this->set($key, $this->container->MediaManager->provider()->byId($thumb_id));
    }

    /**
     * Converts a function, args and args number to a key.
     *
     * @param  string $func    Method name as string
     * @param  array  $argList List of arguments
     * @param  int    $numargs Number of provided args
     * @return string
     */
    public function key(string $func, array $argList = [], int $numargs = 0): string
    {
        $key = $func;

        for ($i = 0; $i < $numargs; $i++)
        {
            $key .= $i . ':' . serialize($argList[$i]) . ';';
        }

        return md5($key);
    }
}
