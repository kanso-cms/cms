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
        'adminAjaxInit'  => [],
        'adminInit'      => [],
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
     * Hook into an event
     *
     * This function is used to hook into a Kanso event externally
     *
     * @param  string    $eventName    The name of the event being fired
     * @param  array     $args         The arguements to be sent to event
     * @return Kanso\Events|false
     */
    public function on($eventName, $callback) {
        if (!is_array($args)) $args = [$args];
        if (isset(self::$events[$eventName])) {
            array_push(self::$events[$eventName], true);
            array_push(self::$callbacks, $callback);
            return $this;
        }
        else {
            return false;
        }
    }

    /**
     * Fire an event
     *
     * This is used internally to dispatch various events throughout
     * the Kanso application. It should not really be used externally 
     * unless you really know what you're doing.
     *
     * @param string    $eventName    The name of the event being fired
     * @param array     $args         The arguements to be sent to event
     */
    public static function fire($eventName, $args = []) 
    {
        if (isset(self::$events[$eventName]) && !empty(self::$events[$eventName])) {
            $events = array_keys(self::$events[$eventName]);
            foreach ($events as $i) {
                # Apply the callback
                \Kanso\Utility\Callback::apply(self::$callbacks[$i], $args);
            }
        }
    }

}