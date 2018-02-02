<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

use kanso\framework\utility\Str;
use kanso\framework\common\ArrayAccessTrait;

/**
 * Response headers
 *
 * @author Joe J. Howard
 */
class Headers
{
    use ArrayAccessTrait;

    private $sent = false;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    { 
    }

    /**
     * Send the headers
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

                if (Str::contains($name, 'http'))
                {
                    header($name.'/1.1 '.$value, true);
                }
                else
                {
                    header($name.':'.$value, true);
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
