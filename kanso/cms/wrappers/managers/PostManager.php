<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\managers;

use kanso\cms\wrappers\managers\Manager;
use kanso\cms\wrappers\providers\PostProvider;

/**
 * Post manager
 *
 * @author Joe J. Howard
 */
class PostManager extends Manager
{
    /**
     * {@inheritdoc}
     */
    public function provider(): PostProvider
	{
        return $this->provider;
	}

    /**
     * Creates a new post
     * 
     * @access public
     * @param  array $row Entry row
     * @return mixed
     */
    public function create(array $row)
    {
        return $this->provider->create($row);
    }

	/**
     * Gets a post by id
     * 
     * @access public
     * @param  int    $id Tag id
     * @return mixed
     */
	public function byId(int $id)
	{
		return $this->provider->byId($id);
	}

    /**
     * Deletes a post by id
     * 
     * @access public
     * @param  string $id Post name id or slug
     * @return bool
     */
    public function delete($id): bool
    {
        $post = $this->byId($id);

        if ($post)
        {
            return $post->delete() ? true : false;
        }
        
        return false;   
    }
}
