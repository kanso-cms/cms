<?php

namespace Kanso;

/**
 * Events
 *
 * This class is used by Kanso to fire events when they happen throughout
 * the application. The idea here is to make customization easier
 * for people and the ability to create 'puligins'.
 *
 * Note that this class is a singleton.
 * 
 */
class Events 
{   
    /**
     * @var Kanso\Events
     */
    private static $instance;

    /**
     * @var mixed Array of callbacks
     */
    protected static $callbacks = [];

    /**
     * @var array    Default Kanso event types
     */
    protected static $events = [
        'newArticle'     => [],
        'articleSave'    => [],
        'articlePublish' => [],
        'articleDelete'  => [],
        'configChange'   => [],
        'login'          => [],
        'logout'         => [],
        'imageUpload'    => [],
        'preDispatch'    => [],
        'midDispatch'    => [],
        'postDispatch'   => [],
        'notFound'       => [],
        'error'          => [],
        'redirect'       => [],
        'adminInit'      => [],
        'htmlEmailSend'  => [],
    ];

    /**
     * Get Events instance (singleton)
     *
     * This creates and/or returns an Events instance (singleton)
     *          
     * @return Kanso\Events
     */
    public static function getInstance() 
    {
        if (!isset(self::$instance)) {
            self::$instance = new Events();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct()
    {

    }

    /**
     * Hook into a filter
     *
     * This function is used to hook into a Kanso filter externally
     *
     * @param  string    $eventName    The name of the filter being fired
     * @param  array     $callback     The callback to user on the event
     *
     */
    public function on($eventName, $callback) {
        self::$callbacks[$eventName][] = $callback;
    }


    /**
     * Fire an event
     *
     * This is used internally to dispatch various events throughout
     * the Kanso application. It should not really be used externally 
     * unless you really know what you're doing.
     *
     * @param string    $eventName     The name of the filter being fired
     * @param array     $args          The arguments to be sent to filter
     */
    public static function fire($eventName, $args = []) 
    {

        # Is there a custom callback for the filter?   
        if (isset(self::$callbacks[$eventName]) && !empty(self::$callbacks[$eventName])) {

            # Loop the filter callbacks
            foreach (self::$callbacks[$eventName] as $filter) {

                # Apply the callback
                \Kanso\Utility\Callback::apply($filter, $args);

            }
        }
    }

}