<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Http\Response;

use Kanso\Framework\Utility\Str;
use Kanso\Framework\Common\ArrayAccessTrait;

/**
 * Response headers
 *
 * @author Joe J. Howard
 */
class Headers
{
    use ArrayAccessTrait;

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
        }
    }

    /**
     * Are the headers sent ?
     *
     * @access public
     */
    public function sent(): bool
    {
        return headers_sent();
    }
}
