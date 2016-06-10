<?php

namespace Kanso\Utility;

/**
 * A consistent way to call a closure or callback
 *
 */
class Callback
{

	/**
	 * Call a callback closure or class method
	 *
	 * @param  mixed      $callback      The callback to call
	 * @param  mixed      $args          The args to call the callback with
	 * @return mixed
	 */
	public static function apply($callback, $args = null)
	{
		# is the callback a string
        if (is_string($callback)) {

            # Are we calling a static method
            if (strpos($callback,'::') !== false) {

                $segments = explode('::', $callback);

                return call_user_func($segments[0].'::'.$segments[1], $args);
            }
            else {

                # grab all parts based on a / separator 
                $parts = explode('/', $callback);

                # collect the last index of the array
                $last = end($parts);

                # grab the controller name and method call
                $segments = explode('@', $last);

                # instanitate controller
                $controller = new $segments[0]($args);

                # call method
                $method = $segments[1];

                return $controller->$method();
                
            }
        }
        else {
            return call_user_func($callback, $args);
        }
	}

}