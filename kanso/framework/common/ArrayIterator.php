<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\common;

/**
 * Array iterator for object implementation.
 *
 * @author Joe J. Howard
 */
class ArrayIterator implements \Iterator
{
    /**
     * Array access.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param array $data Array to iterate (optional) (default [])
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
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
        return current($this->data);
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
        return next($this->data);
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
}
