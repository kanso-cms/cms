<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\event;

use kanso\framework\utility\Callback;

/**
 * Events manager
 *
 * @author Joe J. Howard
 */
class Events 
{   
    /**
     * Instance of self
     *
     * @var \kanso\cms\events\Events
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
     * Get Events instance (singleton)
     *
     * This creates and/or returns an Events instance (singleton)
     *
     * @access public
     * @return \kanso\cms\events\Events
     */
    public static function instance(): Events
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
    public function fire(string $eventName, $args) 
    {
        $result = [$args];

        # Is there a custom callback for the filter?   
        if (isset(self::$callbacks[$eventName]))
        {
            # Loop the filter callbacks
            foreach (self::$callbacks[$eventName] as $callback)
            {
                Callback::apply($callback, $result);
            }
        }
    }
}