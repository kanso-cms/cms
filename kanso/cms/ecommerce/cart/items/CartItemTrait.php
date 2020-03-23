<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\cart\items;

use InvalidArgumentException;

/**
 * Shopping cart item trait.
 *
 * @author Joe J. Howard
 */
trait CartItemTrait
{
    /**
     * Cart item data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Overwrite the data.
     *
     * @param array $data New data to replace
     */
    public function overwrite(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Hashes and returns the items unique id.
     *
     * @return string
     */
    public function getId(): string
    {
        // keys to ignore in the hashing process
        $ignoreKeys = ['quantity'];

        // data to use for the hashing process
        $hashData = $this->data;

        foreach ($ignoreKeys as $key)
        {
            unset($hashData[$key]);
        }

        $hash = md5(serialize($hashData));

        return $hash;
    }

    /**
     * Get a piece of data set on the cart item.
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        switch ($key)
        {
            case 'id':
                return $this->getId();
            default:
                return $this->data[$key];
        }
    }

    /**
     * Set a piece of data on the cart item.
     *
     * @param  string $key   The key to use
     * @param  mixed  $value The value to set
     * @return string
     */
    public function set($key, $value)
    {
        switch ($key)
        {
            case 'quantity':
            case 'product_id':
                $this->setCheckTypeInteger($key, $value);
            break;

            case 'price':
            case 'tax':
            case 'amount':
                $this->setCheckIsNumeric($key, $value);

                $value = floatval($value);
        }

        $this->data[$key] = $value;

        return $this->getId();
    }

    /**
     * Check the value being set is an integer.
     *
     * @param  string                   $key   The key to use
     * @param  mixed                    $value The value to set
     * @throws InvalidArgumentException
     */
    private function setCheckTypeInteger($key, $value): void
    {
        if (!is_int($value))
        {
            throw new InvalidArgumentException(sprintf('%s must be an integer.', $key));
        }
    }

    /**
     * Check the value being set is an integer.
     *
     * @param  string                   $key   The key to use
     * @param  mixed                    $value The value to set
     * @throws InvalidArgumentException
     */
    private function setCheckIsNumeric($key, $value): void
    {
        if (!is_numeric($value))
        {
            throw new InvalidArgumentException(sprintf('%s must be numeric.', $key));
        }
    }

    /**
     * Determine if a piece of data is set on the cart item.
     *
     * @param  string $key The key to use
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of data set on the cart item.
     *
     * @param  string $key The key to use
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a piece of data on the cart item.
     *
     * @param string $key   The key to use
     * @param mixed  $value
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a piece of data from the cart item.
     *
     * @param string $key The key to use
     */
    public function offsetUnset($key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Get a piece of data set on the cart item.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Set a piece of data on the cart item.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Determine if a piece of data is set on the cart item.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Unset a piece of data from the cart item.
     *
     * @param string $key
     */
    public function __unset($key): void
    {
        unset($this->data[$key]);
    }
}
