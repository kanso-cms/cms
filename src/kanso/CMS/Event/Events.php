<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Events;

use Kanso\Framework\Utility\Callback;

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
     * Get Events instance (singleton)
     *
     * This creates and/or returns an Events instance (singleton)
     *
     * @access public
     * @return \Kanso\CMS\Events\Events
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
     * Hook into an event
     *
     * @access public
     * @param  string $eventName The name of the event
     * @param  mixed  $callback  Callback to apply
     * @param  mixed  $args      Args to add (optional) (default null)
     */
    public static function on(string $eventName, $callback, $args = null)
    {
        self::$callbacks[$eventName][] = [$callback, Callback::normalizeArgs($args)];
    }

    /**
     * Fire an event
     *
     * @param string $eventName The name of the event being fired
     * @param mixed  $args      The arguments to be sent to event callback (optional) (default [])
     */
    public static function fire(string $eventName, $args = []) 
    {
        # Convert to an array
        $args = Callback::normalizeArgs($args);

        # Is there a custom callback for the filter?   
        if (isset(self::$callbacks[$eventName]) && !empty(self::$callbacks[$eventName]))
        {
            # Loop the filter callbacks
            foreach (self::$callbacks[$eventName] as $callbackArr)
            {
                Callback::apply($callbackArr[0], array_merge($callbackArr[1], $args));
            }
        }
    }
}
