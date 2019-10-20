<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

use kanso\framework\common\ArrayAccessTrait;
use kanso\framework\common\ArrayIterator;
use kanso\framework\utility\Str;

/**
 * Response headers.
 *
 * @author Joe J. Howard
 */
class Headers implements \IteratorAggregate
{
    use ArrayAccessTrait;

    /**
     * Have the headers been sent?
     *
     * @var bool
     */
    private $sent = false;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Send the headers.
     *
     * @access public
     */
    public function send()
    {
        if (!$this->sent())
        {
            foreach ($this->get() as $name => $value)
            {
                $value = is_array($value) ? reset($value) : $value;

                if (Str::contains($name, 'HTTP'))
                {
                    header($name . '/1.1 ' . $value, true);
                }
                else
                {
                    header($name . ':' . $value, true);
                }
            }

            $this->sent = true;
        }
    }

    /**
     * Are the headers sent ?
     *
     * @access public
     */
    public function sent(): bool
    {
        return $this->sent;
    }
}
