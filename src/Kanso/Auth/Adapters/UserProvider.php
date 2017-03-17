<?php

namespace Kanso\Auth\Adapters;

/**
 * User
 *
 * This class serves as wrapper around the user database. It
 * is used by the gatekeeper to manage authentication and user
 * details.
 *
 */
class UserProvider 
{

	/**
     * @var \Kanso\Kanso::getInstance()->Database()->Builder()
     */
    private $SQL;

	/**
     * Constructor
     *
     */
    public function __construct()
    {
        # Get and SQL builder
        $this->SQL = \Kanso\Kanso::getInstance()->Database()->Builder();
    }

    /**
     * Get a single user by id
     *
     * @param  int    $id    The user id
     * @return \Kanso\Auth\Adapters\User|false
     *
     */
    public function byId($id)
    {
    	$row = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', intval($id))->ROW();

    	if ($row) return new \Kanso\Auth\Adapters\User($row);

    	return false;
    }

    /**
     * Get a
     *
     * @param  string     $key    posts table column name
     * @param  string     $value  posts table column value
     * @param  boolean    $row    Return a single row or all users
     * @return array of \Kanso\Auth\Adapters\User or 
     *
     */
    public function byKey($key, $value, $row = false)
    {
    	$users = [];
    	if ($row) {
    		$row = $this->SQL->SELECT('*')->FROM('users')->WHERE($key, '=', $value)->ROW();
    		if ($row) $users[] = new \Kanso\Auth\Adapters\User($row);
    	}
    	else {
    		$rows = $this->SQL->SELECT('*')->FROM('users')->WHERE($key, '=', $value)->FIND_ALL();
    		if (!empty($rows)) {
    			foreach ($rows as $row) {
    				$users[] = new \Kanso\Auth\Adapters\User($row);
    			}
    		} 
    	}
    	return $users;
    }


}



