<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\managers;

use kanso\cms\wrappers\providers\CategoryProvider;
use kanso\framework\utility\Str;

/**
 * Category manager.
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
	 * Creates a new category.
	 *
	 * @param  string      $name Category name
	 * @param  string|null $slug Category slug (optional) (default null)
	 * @return mixed
	 */
	public function create(string $name, string $slug = null)
	{
		$slug = !$slug ? Str::slug($name) : Str::slug($slug);

		$catExists = $this->provider->byKey('slug', $slug, true);

		if ($catExists)
		{
			return false;
		}

		return $this->provider->create(['name' => $name, 'slug' => $slug]);
	}

	/**
	 * Gets a category by id.
	 *
	 * @param  int   $id Category id
	 * @return mixed
	 */
	public function byId(int $id)
	{
		return $this->provider->byId($id);
	}

	/**
	 * Gets a category by name.
	 *
	 * @param  string $name Category name
	 * @return mixed
	 */
	public function byName(string $name)
	{
		return $this->provider->byKey('name', $name, true);
	}

	/**
	 * Gets a category by slug.
	 *
	 * @param  string $slug Category slug
	 * @return mixed
	 */
	public function bySlug(string $slug)
	{
		return $this->provider->byKey('slug', $slug, true);
	}

	/**
	 * Deletes a category by name id or slug.
	 *
	 * @param  string $nameIdOrSlug Category name id or slug
	 * @return bool
	 */
	public function delete($nameIdOrSlug): bool
	{
		$cat = false;

		if (is_int($nameIdOrSlug))
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
			return $cat->delete() ? true : false;
		}

		return false;
	}

	/**
	 * Clears a category by name id or slug.
	 *
	 * @param  string $nameIdOrSlug Category name id or slug
	 * @return bool
	 */
	public function clear($nameIdOrSlug): bool
	{
		$cat = false;

		if (is_int($nameIdOrSlug))
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
			return $cat->clear() ? true : false;
		}

		return false;
	}
}
