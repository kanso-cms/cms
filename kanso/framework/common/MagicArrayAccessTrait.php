<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\common;

/**
 * Array access magic methods trait.
 *
 * @author Joe J. Howard
 */
trait MagicArrayAccessTrait
{
	/**
	 * Array access.
	 *
	 * @var array
	 */
	protected $data = [];

    /**
     * Return all properties.
     *
     * @return array
     */
    public function asArray(): array
    {
        return $this->data;
    }

    /**
     * Get a property by key.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        if (isset($this->data[$key]))
        {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Set a property by key.
     */
    public function __set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if a property by key exists.
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Unset a property by key.
     */
    public function __unset(string $key): void
    {
        if (isset($this->data[$key]))
        {
            unset($this->data[$key]);
        }
    }
}
