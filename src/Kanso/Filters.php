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
     * @var array    Default Kanso event types
     */
    protected static $filters = [
        'configChange'        => [],
        'adminArticleTabs'    => [],
        'adminFavicons'       => [],
        'adminPageTitle'      => [],
        'adminSettingsTabs'   => [],
        'adminHeaderScripts'  => [],
        'adminBodyClass'      => [],
        'adminSvgSprites'     => [],
        'adminHeaderLinks'    => [],
        'adminHeaderDropdown' => [],
        'adminTabNav'         => [],
        'adminTabPanels'      => [],
        'adminDropDown'       => [],
        'adminFooterScripts'  => [],
        'adminPostTypes'      => [],
        'adminAjaxResponse'   => [],
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
    public function on($eventName, $callback) {
        if (!is_array($args)) $args = [$args];
        if (isset(self::$filters[$eventName])) {
            array_push(self::$filters[$eventName], true);
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
    public static function apply($filterName, $filterData) 
    {
        
        # Is there a custom callback for the filter?   
        if (isset(self::$filters[$filterName]) && !empty(self::$filters[$filterName])) {

            # Return the supplied data to filtered
            $result  = $filterData;

            # Get all the filters callbacks for the filter
            $filters = array_keys(self::$filters[$filterName]);

            # Loop the filter callbacks
            foreach ($filters as $filter) {

                # Apply the callback
                $result = \Kanso\Utility\Callback::apply(self::$callbacks[$filter], $filterData);

            }

            return $result;
        }
        else {
            return $filterData;
        }
    }

}