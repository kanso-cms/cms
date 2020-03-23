<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\managers;

use kanso\cms\wrappers\managers\Manager;
use kanso\cms\ecommerce\providers\BundleProvider;

/**
 * Bundle manager.
 *
 * @author Joe J. Howard
 */
class BundleManager extends Manager
{
    /**
     * {@inheritdoc}
     */
    public function provider(): BundleProvider
	{
        return $this->provider;
	}

    /**
     * Creates a new product.
     *
     * @param  array $row Entry row
     * @return mixed
     */
    public function create(array $row)
    {
        return $this->provider->create($row);
    }

	/**
	 * Gets a product by id.
	 *
	 * @param  int   $id Tag id
	 * @return mixed
	 */
	public function byId(int $id)
	{
		return $this->provider->byId($id);
	}

    /**
     * Deletes a product by id.
     *
     * @param  int  $id Post id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $post = $this->byId($id);

        if ($post)
        {
            return $post->delete() ? true : false;
        }

        return false;
    }
}
