<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers\Managers;

use Kanso\CMS\Wrappers\Managers\Manager;
use Kanso\CMS\Wrappers\Providers\CategoryProvider;
use Kanso\Framework\Utility\Str;

/**
 * Category manager
 *
 * @author Joe J. Howard
 */
class CategoryManager extends Manager
{
    /**
     * {@inheritdoc}
     */
    public function provider(): CategoryProvider
	{
        return $this->provider;
	}

	/**
     * Creates a new category
     * 
     * @access public
     * @param  string $name Category name
     * @param  string $slug Category slug (optional) (default null)
     * @return mixed
     */
	public function create(string $name, string $slug = null)
	{
		$slug = !$slug ? Str::slug($name) : Str::slug($slug);

		$catExists = $this->provider->byKey('slug', $slug, true);

		if ($catExists)
		{
			return $catExists;
		}
		
		return $this->provider->create(['name' => $name, 'slug' => $slug]);
	}

	/**
     * Gets a category by id
     * 
     * @access public
     * @param  int    $id Category id
     * @return mixed
     */
	public function byId(int $id)
	{
		return $this->provider->byId($id);
	}


	/**
     * Gets a category by name
     * 
     * @access public
     * @param  string $name Category name
     * @return mixed
     */
	public function byName(string $name)
	{
		return $this->provider->byKey('name', $name, true);
	}

	/**
     * Gets a category by slug
     * 
     * @access public
     * @param  string $slug Category slug
     * @return mixed
     */
	public function bySlug(string $slug)
	{
		return $this->provider->byKey('slug', $slug, true);
	}

	/**
     * Deletes a category by name id or slug
     * 
     * @access public
     * @param  string $nameIdOrSlug Category name id or slug
     * @return bool
     */
	public function delete($nameIdOrSlug): bool
	{
		$cat = false;

		if (is_integer($nameIdOrSlug))
		{
			$cat = $this->byId($nameIdOrSlug);
		}
		else
		{
			$cat = $this->byName($nameIdOrSlug);

			if (!$cat)
			{
				$cat = $this->bySlug($nameIdOrSlug);
			}
		}

		if ($cat)
		{
			return $cat->delete();
		}
		
		return false;	
	}

	/**
     * Clears a category by name id or slug
     * 
     * @access public
     * @param  string $nameIdOrSlug Category name id or slug
     * @return bool
     */
	public function clear($nameIdOrSlug): bool
	{
		$cat = false;

		if (is_integer($nameIdOrSlug))
		{
			$cat = $this->byId($nameIdOrSlug);
		}
		else
		{
			$cat = $this->byName($nameIdOrSlug);

			if (!$cat)
			{
				$cat = $this->bySlug($nameIdOrSlug);
			}
		}

		if ($cat)
		{
			return $cat->clear();
		}
		
		return false;	
	}
}
