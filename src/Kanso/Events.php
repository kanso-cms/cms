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
     * @var mixed Array of callback arguements
     */
    protected static $callbackArgs = [];

    /**
     * @var array    Default Kanso event types
     */
    protected static $events = [

        /**
         * @var   array              Fired right after a new article is created.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'newArticle'     => [],

        /**
         * @var   array              Fired right after an existing article is saved.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'articleSave'    => [],

        /**
         * @var   array              Fired right after an existing article is published.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'articlePublish' => [],

        /**
         * @var   array              Fired right before an existing article is deleted.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'articleDelete'  => [],

        /**
         * @var   array              Fired right after a change is made to the sitemap
         * @param array    $XML      recieves the sitemap XML as a paramteter
         */
        'siteMapChange'  => [],

        /**
         * @var   array               Fired right after Kanso's configuration is updated
         * @param array    $Config    Recieves Knaso's configuration as a parameter
         */
        'configChange'   => [],
    
        /**
         * @var   array                Fired when a user logs into to Kanso
         * @param array    $SESSION    Recieves the raw Admins row from the database as a parameter
         */
        'login'          => [],

        /**
         * @var   array                Fired right before a user logs out of Kanso
         * @param array    $SESSION    Recieves the raw Admins row from the database as a parameter
         */
        'logout'         => [],

        /**
         * @var   array               Fired right after an image is uploaded and saved to the server (this includes multiple sizes of the same image)
         * @param string    $DST      Recieves the absolute path to image that was uploaded
         */
        'imageUpload'    => [],

        /**
         * @var   array               Fired right before Kanso's router is dispatched. 
         * @param null                Recieves no parameters
         */
        'preDispatch'    => [],

        /**
         * @var   array                Fired after the router is dispatched but before
         *                             the headers, body and status are sent, recieves 
         * @param mixed                Recieves the $status, $headers, $body as parameters
         */
        'midDispatch'    => [],

        /**
         * @var   array               Fired after dispatching has finsished and content is sent to the client.
         * @param null                Recieves no parameters
         */
        'postDispatch'   => [],

        /**
         * @var   array               Fired directly before a 404 response is sent.
         * @param mixed               Recieves the $REQUEST_URI, and a unix_timestamp as parameters
         */
        'notFound'       => [],

        /**
         * @var   array                Fired right before an exception is thrown or an error occurs
         * @param mixed                Recieves The numeric type of the Error, 
         *                             The error message, 
         *                             The absolute path to the affected file,
         *                             The line number of the error in the affected file
         *                             and a unix timestamp as parameters
         */
        'error'          => [],

        /**
         * @var   array               Fired directly before a redirect happens
         * @param mixed               Recieves the REQUEST URI, the status, and a unix timestamp
         */
        'redirect'       => [],
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
    public function on($eventName, $callback, $args = []) {
        if (!is_array($args)) $args = [$args];
        if (isset(self::$events[$eventName])) {
            array_push(self::$events[$eventName], true);
            array_push(self::$callbacks, $callback);
            array_push(self::$callbackArgs, $args);
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
                call_user_func_array(self::$callbacks[$i], array_merge($args, self::$callbackArgs[$i]));
            }
        }
    }

}