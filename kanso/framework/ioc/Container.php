<?php
/**
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     2.6.3
 * @package     Slim
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace kanso\framework\ioc;

use ArrayIterator;
use Closure;

class Container implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Key-value array of arbitrary data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Singleton instance of self.
     *
     * @var \kanso\framework\ioc\Container
     */
    private static $instance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Get the global Container instance.
     *
     * @return \kanso\framework\ioc\Container
     */
    public static function instance(): Container
    {
        if (!empty(static::$instance))
        {
            return static::$instance;
        }

        return static::$instance = new static;
    }

    /**
     * Normalize data key.
     *
     * Used to transform data key into the necessary
     * key format for this set. Used in subclasses
     * like \kanso\framework\http\Headers.
     *
     * @param  string $key The data key
     * @return mixed  The transformed/normalized data key
     */
    protected function normalizeKey($key)
    {
        return $key;
    }

    /**
     * Set data key to value.
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function set($key, $value): void
    {
        $this->data[$this->normalizeKey($key)] = $value;
    }

    /**
     * Get data value with key.
     * @param  string $key     The data key
     * @param  mixed  $default The value to return if data key does not exist
     * @return mixed  The data value, or the default value
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            $isInvokable = is_object($this->data[$this->normalizeKey($key)]) && method_exists($this->data[$this->normalizeKey($key)], '__invoke');

            return $isInvokable ? $this->data[$this->normalizeKey($key)]($this) : $this->data[$this->normalizeKey($key)];
        }

        return $default;
    }

    /**
     * Add data to set.
     * @param array $items Key-value array of data to append to this set
     */
    public function replace($items): void
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value); // Ensure keys are normalized
        }
    }

    /**
     * Fetch set data.
     * @return array This set's key-value data array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Fetch set data keys.
     * @return array This set's key-value data array keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Does this set contain a key?
     * @param  string $key The data key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($this->normalizeKey($key), $this->data);
    }

    /**
     * Remove value with key from this set.
     * @param string $key The data key
     */
    public function remove($key): void
    {
        unset($this->data[$this->normalizeKey($key)]);
    }

    /**
     * Property Overloading.
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value): void
    {
        $this->set($key, $value);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key): void
    {
        $this->remove($key);
    }

    /**
     * Clear all values.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Array Access.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * Countable.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * IteratorAggregate.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Ensure a value or object will remain globally unique.
     * @param  string   $key   The value or object name
     * @param  \Closure $value The closure that defines the object
     * @return mixed
     */
    public function singleton($key, $value)
    {
        $this->set($key, function($c) use ($value)
        {
            static $object;

            if (null === $object) {
                $object = $value($c);
            }

            return $object;
        });

        return $this;
    }

    /**
     * Ensure a value or object will remain globally unique.
     * @param string $key    The value or object name
     * @param object $object The closure that defines the object
     */
    public function setInstance($key, $object): void
    {
        $this->set($key, function() use ($object)
        {
            return $object;
        });
    }

    /**
     * Protect closure from being directly invoked.
     * @param  \Closure $callable A closure to keep from being invoked and evaluated
     * @return Closure
     */
    public function protect(Closure $callable)
    {
        return function() use ($callable) {
            return $callable;
        };
    }
}
