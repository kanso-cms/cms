<?php

namespace Kanso;

/**
 * 
 */
class Filters 
{   
    /**
     * @var Kanso\Filters
     */
    private static $instance;

    /**
     * @var array    Default Kanso filter types
     */
    protected static $callbacks = [
        'configChange'        => [],
        'adminPageTitle'      => [],
        'adminFavicons'       => [],
        'adminHeaderScripts'  => [],
        'adminBodyClass'      => [],
        'adminSvgSprites'     => [],
        'adminHeaderLinks'    => [],
        'adminHeaderDropdown' => [],
        'adminTabNav'         => [],
        'adminTabPanels'      => [],
        'adminFooterScripts'  => [],
        'adminPostTypes'      => [],
        'adminSettingsTabs'   => [],
        'adminAjaxResponse'   => [],
        'emailBody'           => [],
    ];

    /**
     * Get Filters instance (singleton)
     *
     * This creates and/or returns an Filters instance (singleton)
     *          
     * @return Kanso\Filters
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
     * Hook into a filter
     *
     * This function is used to hook into a Kanso filter externally
     *
     * @param  string    $filterName    The name of the filter being fired
     * @param  array     $args         The arguements to be sent to filter
     * @return Kanso\Events|false
     */
    public function on($filterName, $callback) {
        self::$callbacks[$filterName][] = $callback;
    }

    /**
     * Fire a filter
     *
     * This is used internally to dispatch various events throughout
     * the Kanso application. It should not really be used externally 
     * unless you really know what you're doing.
     *
     * @param string    $filterName    The name of the filter being fired
     * @param array     $args          The arguements to be sent to filter
     */
    public static function apply($filterName, $args) 
    {
 
        # Is there a custom callback for the filter?   
        if (isset(self::$callbacks[$filterName]) && !empty(self::$callbacks[$filterName])) {

            # Return the supplied data to filtered
            $result  = $args;

            # Loop the filter callbacks
            foreach (self::$callbacks[$filterName] as $filter) {

                # Apply the callback
                $result = \Kanso\Utility\Callback::apply($filter, $args);

            }

            return $result;
        }
        else {
            return $args;
        }
    }

}