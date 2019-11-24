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
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
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
     * @return array
     */
    public function getRaw(): array
    {
        return $this->data;
    }

    /**
     * Set the raw data including the iterators.
     */
    public function putRaw(array $data): void
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
     * @param  string|null $key The key to use for the value (optional) (default null)
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
     * @param string $key   The key to use for the value
     * @param mixed  $value The value to store
     */
    public function put(string $key, $value): void
    {
        Arr::set($this->data, $key, ['key' => $value, 'count' => 0]);
    }

    /**
     * Save a key/value pair to the flash data.
     *
     * @param array $data Array of data to save
     */
    public function putMultiple(array $data): void
    {
        foreach ($data as $key => $value)
        {
            $this->put($key, $value);
        }
    }

    /**
     * Remove a key-value from the flash data.
     *
     * @param string $key They key to remove the value with
     */
    public function remove(string $key): void
    {
        Arr::delete($this->data, $key);
    }

    /**
     * Clear the sessions flash data.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Loop over the flash data and remove old data.
     */
    public function iterate(): void
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
