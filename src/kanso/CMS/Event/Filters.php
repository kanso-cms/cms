<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Event;

use Kanso\Framework\Utility\Callback;

/**
 * Filters manager
 *
 * @author Joe J. Howard
 */
class Filters 
{   
    /**
     * Instance of self
     *
     * @var \Kanso\CMS\Events\Events
     */
    private static $instance;

    /**
     * List of callbacks
     *
     * @var mixed Array of callbacks
     */
    protected static $callbacks = [];

    /**
     * Private constructor
     *
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Get Filters instance (singleton)
     *
     * This creates and/or returns an Filters instance (singleton)
     *
     * @access public
     * @return \Kanso\CMS\Events\Filters
     */
    public static function instance(): Filters
    {
        if (!isset(self::$instance))
        {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * Hook into an filter
     *
     * @access public
     * @param  string $eventName The name of the filter
     * @param  mixed  $callback  Callback to apply
     * @param  mixed  $args      Args to add (optional) (default null)
     */
    public function on(string $eventName, $callback)
    {    
        self::$callbacks[$eventName][] = $callback;
    }

    /**
     * Apply a filter
     *
     * @param string $eventName The name of the filter being fired
     * @param mixed  $args      The arguments to be sent to filter callback (optional) (default [])
     */
    public function apply(string $eventName, $args) 
    {
        $result = [$args];

        # Is there a custom callback for the filter?   
        if (isset(self::$callbacks[$eventName]))
        {
            # Loop the filter callbacks
            foreach (self::$callbacks[$eventName] as $callback)
            {
                $result = Callback::apply($callback, $result);
            }
        }
        else
        {
            return $args;
        }

        return $result;
    }
}
