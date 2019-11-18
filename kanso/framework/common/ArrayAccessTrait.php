<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\common;

use kanso\framework\utility\Arr;

/**
 * Array access trait.
 *
 * @author Joe J. Howard
 */
trait ArrayAccessTrait
{
	/**
	 * Array access.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Save a key to the array using dot notation.
	 *
	 * @param string $key   Key to use
	 * @param mixed  $value Value to save
	 */
	public function set(string $key, $value)
	{
		Arr::set($this->data, $key, $value);

		return $value;
	}

	/**
	 * Alias for set.
	 *
	 * @param string $key   Key to use
	 * @param mixed  $value Value to save
	 */
	public function put(string $key, $value)
	{
		return $this->set($key, $value);
	}

	/**
	 * Save an array of key values using dot notation.
	 *
	 * @param array $data Associative array to add
	 */
	public function setMultiple(array $data): void
	{
		foreach ($data as $key => $value)
		{
			if (strpos($key, '.') !== false)
			{
				Arr::set($this->data, $key, $value);
			}
			else
			{
				$this->data[$key] = $value;
			}
		}
	}

	/**
	 * Check if the internal array has a value using dot notation.
	 *
	 * @param  string $key Key to use
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return Arr::has($this->data, $key);
	}

	/**
	 * Get a key/value from the internal array using dot notation.
	 *
	 * @param  string|null $key Key to use (optional) (default null)
	 * @return mixed
	 */
	public function get(string $key = null)
	{
		if (!$key)
		{
			return $this->data;
		}

		return Arr::get($this->data, $key);
	}

	/**
	 * Remove a key/value from the internal array using dot notation.
	 *
	 * @param string $key Key to use
	 */
	public function remove(string $key): void
	{
		Arr::delete($this->data, $key);
	}

	/**
	 * Empty the internal array.
	 */
	public function clear(): void
	{
		$this->data = [];
	}

	/**
	 * Overwrite the internal array with a new one.
	 *
	 * @param array $data Array to overwrite the internal array with
	 */
	public function overwrite(array $data): void
	{
		$this->data = [];

		$this->setMultiple($data);
	}

	/**
	 * Alias for get.
	 *
	 * @return array
	 */
	public function asArray(): array
	{
		return $this->get();
	}
}
