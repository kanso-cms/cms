<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Common;

/**
 * Array access magic methods trait.
 *
 * @author Joe J. Howard
 */
trait MagicArrayAccessTrait
{
	/**
	 * Array access
	 *
	 * @var string
	 */
	protected $data = [];

	/**
     * Return all properties
     *
     * @access public
     * @return array
     */
    public function asArray(): array
    {
        return $this->data;
    }

    /**
     * Get a property by key
     *
     * @access public
     * @return string|null
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
     * Set a property by key
     *
     * @access public
     */
    public function __set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if a property by key exists
     *
     * @access public
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Unset a property by key
     *
     * @access public
     */
    public function __unset(string $key)
    {
        if (isset($this->data[$key]))
        {
            unset($this->data[$key]);
        } 
    }
}