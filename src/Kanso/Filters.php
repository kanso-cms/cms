<?php

namespace Kanso;

/**
 * 
 */
class Filters 
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
    protected static $filters = [

        /**
         * @var   array              Fired right before a new article is created.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'newArticle'     => [],

        /**
         * @var   array              Fired right before an existing article is saved.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'articleSave'    => [],

        /**
         * @var   array              Fired right before an existing article is published.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'articlePublish' => [],

        /**
         * @var   array              Fired right before an existing article is deleted.
         * @param array    $Entry    Recieves the article row from the database as a parameter
         */
        'articleDelete'  => [],

        /**
         * @var   array               Fired right before Kanso's configuration is updated
         * @param array    $Config    Recieves Knaso's configuration as a parameter
         */
        'configChange'   => [],

        /**
         * @var   array               Fired right before Kanso's configuration is updated
         * @param array    $Config    Recieves Knaso's configuration as a parameter
         */
        'adminArticleTabs'   => [],

        /**
         * @var   array               Fired right before Kanso's configuration is updated
         * @param array    $Config    Recieves Knaso's configuration as a parameter
         */
        'adminSettingsTabs'   => [],

        /**
         * @var   array               Fired right before Kanso's configuration is updated
         * @param array    $Config    Recieves Knaso's configuration as a parameter
         */
        'adminDropDown'   => [],

        'adminFavicons'   => [],
    
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
            self::$instance = new Filters();
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
        if (isset(self::$filters[$eventName])) {
            array_push(self::$filters[$eventName], true);
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
    public static function apply($filterName, $filterData, $args = []) 
    {
        
        # Is there a custom callback for the filter?   
        if (isset(self::$filters[$filterName]) && !empty(self::$filters[$filterName])) {

            # Return the supplied data to filtered
            $result  = $filterData;

            # Get all the filters callbacks for the filter
            $filters = array_keys(self::$filters[$filterName]);

            # Loop the filter callbacks
            foreach ($filters as $filter) {

                # is the callback a string
                if (is_string(self::$callbacks[$filter])) {

                    $callback = self::$callbacks[$filter];
                    $args     = array_merge($args, self::$callbackArgs[$filter]);

                    # Are we calling a static method
                    if (strpos($callback, '::') !== false) {

                        $segments = explode('::', $callback);

                        $result = call_user_func($segments[0].'::'.$segments[1], $args);

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
                        $result = $controller->$segments[1]();
                    }
                }
                else {

                    $result = call_user_func(self::$callbacks[$filter], array_merge($args, self::$callbackArgs[$filter]));
                }
            }

            return $result;
        }
        else {
            return $filterData;
        }
    }

}