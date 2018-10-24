<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session;

use kanso\framework\utility\Arr;

/**
 * Session Manager.
 *
 * @author Joe J. Howard
 */
class Flash implements \Iterator
{
    /**
     * The session flash data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor.
     *
     * @access public
     * @param  $configuration array Array of configuration options
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->data)['key'];
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->data)['key'];
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $key = key($this->data);

        $data = ($key !== null && $key !== false);

        return $data;
    }

    /**
     * Get the raw data including the iterators.
     *
     * @access public
     * @return array
     */
    public function getRaw(): array
    {
        return $this->data;
    }

    /**
     * Set the raw data including the iterators.
     *
     * @access public
     */
    public function putRaw(array $data)
    {
        $result = [];

        foreach ($data as $key => $value)
        {
            if (isset($value['key']) && isset($value['count']))
            {
                $result[$key] =
                [
                    'key'   => $value['key'],
                    'count' => intval($value['count']),
                ];
            }
        }

        $this->data = $result;
    }

    /**
     * Get a key from the flash data or the entire flash data.
     *
     * @access public
     * @param  string $key The key to use for the value (optional) (default null)
     * @return mixed
     */
    public function get(string $key = null)
    {
        if (!$key) {

            $data = [];

            foreach ($this->data as $key => $value)
            {
                $data[$key] = $value['key'];
            }

            return $data;
        }

        if ($this->has($key))
        {
            return Arr::get($this->data, $key . '.key');
        }

        return null;
    }

    /**
     * Check if a key-value exists in the flash data.
     *
     * @access public
     * @param  string $key The key to use for the value
     * @return bool
     */
    public function has(string $key): bool
    {
        return Arr::has($this->data, $key . '.key');
    }

    /**
     * Save a key/value pair to the flash data.
     *
     * @access public
     * @param string $key   The key to use for the value
     * @param mixed  $value The value to store
     */
    public function put(string $key, $value)
    {
        Arr::set($this->data, $key, ['key' => $value, 'count' => 0]);
    }

    /**
     * Save a key/value pair to the flash data.
     *
     * @access public
     * @param array $data Array of data to save
     */
    public function putMultiple(array $data)
    {
        foreach ($data as $key => $value)
        {
            $this->put($key, $value);
        }
    }

    /**
     * Remove a key-value from the flash data.
     *
     * @access public
     * @param string $key They key to remove the value with
     */
    public function remove(string $key)
    {
        Arr::delete($this->data, $key);
    }

    /**
     * Clear the sessions flash data.
     *
     * @access public
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Loop over the flash data and remove old data.
     *
     * @access public
     */
    public function iterate()
    {
        foreach ($this->data as $key => $value)
        {
            if ($value['count'] + 1 > 1)
            {
                unset($this->data[$key]);
            }
            else
            {
                $this->data[$key]['count'] = $this->data[$key]['count'] + 1;
            }
        }
    }
}
