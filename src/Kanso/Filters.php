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
     * @var array    Default Kanso filter types
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
        if (!is_array($args)) $args = [$args];
        if (isset(self::$filters[$filterName])) {
            array_push(self::$filters[$filterName], true);
            array_push(self::$callbacks, $callback);
            return $this;
        }
        else {
            return false;
        }
    }

    /**
     * Fire a filter
     *
     * This is used internally to dispatch various events throughout
     * the Kanso application. It should not really be used externally 
     * unless you really know what you're doing.
     *
     * @param string    $filterName    The name of the filter being fired
     * @param array     $filterData     The arguements to be sent to filter
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