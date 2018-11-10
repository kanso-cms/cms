<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\managers;

use kanso\cms\wrappers\providers\TagProvider;
use kanso\framework\utility\Str;

/**
 * Tag manager.
 *
 * @author Joe J. Howard
 */
class TagManager extends Manager
{
    /**
     * {@inheritdoc}
     */
    public function provider(): TagProvider
	{
        return $this->provider;
	}

	/**
	 * Creates a new tag.
	 *
	 * @access public
	 * @param  string $name Tag name
	 * @param  string $slug Tag slug (optional) (default null)
	 * @return mixed
	 */
	public function create(string $name, string $slug = null)
	{
		$slug = !$slug ? Str::slug($name) : Str::slug($slug);

		$tagExists = $this->provider->byKey('slug', $slug, true);

		if ($tagExists)
		{
			return false;
		}

		return $this->provider->create(['name' => $name, 'slug' => $slug]);
	}

	/**
	 * Gets a tag by id.
	 *
	 * @access public
	 * @param  int   $id Tag id
	 * @return mixed
	 */
	public function byId(int $id)
	{
		return $this->provider->byId($id);
	}

	/**
	 * Gets a tag by name.
	 *
	 * @access public
	 * @param  string $name Tag name
	 * @return mixed
	 */
	public function byName(string $name)
	{
		return $this->provider->byKey('name', $name, true);
	}

	/**
	 * Gets a tag by slug.
	 *
	 * @access public
	 * @param  string $slug Tag slug
	 * @return mixed
	 */
	public function bySlug(string $slug)
	{
		return $this->provider->byKey('slug', $slug, true);
	}

	/**
	 * Deletes a tag by name id or slug.
	 *
	 * @access public
	 * @param  string $nameIdOrSlug Tag name id or slug
	 * @return bool
	 */
	public function delete($nameIdOrSlug): bool
	{
		$tag = false;

		if (is_int($nameIdOrSlug))
		{
			$tag = $this->byId($nameIdOrSlug);
		}
		else
		{
			$tag = $this->byName($nameIdOrSlug);

			if (!$tag)
			{
				$tag = $this->bySlug($nameIdOrSlug);
			}
		}

		if ($tag)
		{
			return $tag->delete() ? true : false;
		}

		return false;
	}

	/**
	 * Clears a tag by name id or slug.
	 *
	 * @access public
	 * @param  string $nameIdOrSlug Tag name id or slug
	 * @return bool
	 */
	public function clear($nameIdOrSlug): bool
	{
		$tag = false;

		if (is_int($nameIdOrSlug))
		{
			$tag = $this->byId($nameIdOrSlug);
		}
		else
		{
			$tag = $this->byName($nameIdOrSlug);

			if (!$tag)
			{
				$tag = $this->bySlug($nameIdOrSlug);
			}
		}

		if ($tag)
		{
			return $tag->clear() ? true : false;
		}

		return false;
	}
}
