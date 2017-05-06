<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Utility;

use ReflectionClass;

/**
 * Callback helper
 *
 * @author Joe J. Howard
 */
class Callback
{
	/**
	 * Call a callback closure or class method
	 *
     * @access public
	 * @param  mixed $callback  The callback to call
	 * @param  mixed $args      The args to call the callback with
	 * @return mixed
	 */
	public static function apply($callback, $args = null)
	{
        $args = self::normalizeArgs($args);
        
		# is the callback a string
        if (is_string($callback))
        {
            # Are we calling a static method
            if (strpos($callback,'::') !== false)
            {
                $segments = explode('::', $callback);

                return call_user_func_array([$segments[0], $segments[1]], $args);
            }
            else
            {
                # grab all parts based on a / separator 
                $parts = explode('/', $callback);

                # collect the last index of the array
                $last = end($parts);

                # grab the class name and method call
                $segments = explode('@', $last);

                # instantiate the class
                $class = self::newClass($segments[0], $args);

                # call method
                $method = $segments[1];

                return $class->$method();
            }
        }
        else
        {
            return call_user_func_array($callback, $args);
        }
	}

    /**
     * Returns a new class object by name with args
     *
     * @access public
     * @param  string $class The class name to instantiate
     * @param  array   $args Array of args to apply to class constructor
     * @return object
     */
    public static function newClass(string $class, array $args = [])
    {
        return call_user_func_array([new ReflectionClass($class), 'newInstance'], $args);
    }

    /**
     * Converts args to array
     *
     * @access public
     * @param  mixed $args The args to call the callback with
     * @return array
     */
    public static function normalizeArgs($args): array
    {
        return is_null($args) ? [] : (!is_array($args) ? [$args] : $args);
    }
}
