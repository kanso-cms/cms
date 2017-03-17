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
class User 
{
	/**
     * Row data from/to the database
     *
     * @var array
     */
	private $data = [];

	/**
     * If the user's slug gets changed in any way
     *
     * @var string
     */
	private $slug;

    /**
     * @var \Kanso\Kanso::getInstance()->Database()->Builder()
     */
    private $SQL;

	/**
     * Constructor
     * 
     * @param mixed    $rowOrId    Array from Database row or user ID
     *
     */
    public function __construct($rowOrId)
    {
        $this->SQL = \Kanso\Kanso::getInstance()->Database()->Builder();

    	if (is_array($rowOrId)) {
    		$this->applyRow($rowOrId);
    	}
    	else if (is_numeric($rowOrId) || is_int($rowOrId)) {
    		$entry = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', intval($rowOrId))->ROW();
            $this->applyRow($entry);
    	}
    }

    /********************************************************************************
    * PUBLIC METHODS
    *******************************************************************************/

    /**
     * Save the user to the database - new or existing
     *
     * @return boolean
     */
    public function save()
	{
        $saved = false;
        if (!isset($this->data['id'])) {
            $saved = $this->SQL->INSERT_INTO('users')->VALUES($this->data)->QUERY();
        }
        else {
            $saved = $this->SQL->UPDATE('users')->SET($this->data)->WHERE('id', '=', $this->data['id'])->QUERY();
        }

        if ($saved) {
            # If the user's slug was changed, we need to update kanso's config      
            if ($this->data['role'] === 'administrator' || $this->data['role'] === 'writer') {

                # Remove old slug if it was changed
                $this->updateSlugs();

            }
            return true;
        }

        return false;
	}

	/**
     * Delete this user from the database
     *
     * @return boolean
     */
    public function delete()
	{
		# Must be an id set
		if (!isset($this->data['id'])) return false;

		# Update the database
		$delete = $this->SQL->DELETE_FROM('users')->WHERE('id', '=', $this->data['id'])->QUERY();
		
        if ($delete) {

            # If the user's slug was changed, we need to update kanso's config      
            if ($this->data['role'] === 'administrator' || $this->data['role'] === 'writer') {

                # Change all their posts
                $this->SQL->UPDATE('posts')->SET(['author_id' => 1])->WHERE('author_id', '=', $this->data['id'])->QUERY();

                # Remove slug
                $this->removeSlug();

            }
            return true;
        }

        return false;
	}

    /**
     * Generate an access token for this user and save it
     *
     * @return boolean
     */
    public function generateAccessToken()
    {
        $this->data['access_token'] = \Kanso\Utility\Str::generateRandom(16, true);
        return $this->save();
    }

    /********************************************************************************
	* MAGIC METHOD OVVERIDES
	*******************************************************************************/

	public function __get($key)
	{
		$key = $this->normalizeKey($key);
		if (array_key_exists($key, $this->data)) return $this->data[$key];
		
		return null;
	}

	public function __set($key, $value)
	{
		$key = $this->normalizeKey($key);
		$this->data[$key] = $value;
	}

	public function __isset($key)
	{
		$key = $this->normalizeKey($key);
		return array_key_exists($key, $this->data);
	}

	public function __unset($key)
	{
		$key = $this->normalizeKey($key);
		if (array_key_exists($key, $this->data)) {
			$this->data[$key] = '';
		}
	}
	
	/********************************************************************************
	* PRIVATE HELPERS
	*******************************************************************************/

    /**
     * Apply a database row to the user
     *
     * @param  array     $row      Array from Database row
     */
    private function applyRow($row)
    {
    	foreach ($row as $key => $value) {
    		$key = $this->normalizeKey($key);
    		$this->data[$key] = $value;
    		if ($key === 'slug') $this->slug = $value;
    	}
    }

    /**
     * Make sure the key is valid
     *
     * @param  string     $key      Database column
     */
    private function normalizeKey($key)
	{
		$key = strval($key);
		if ($key === 'password') return 'hashed_pass';
		return $key;
	}

	/**
     * If the author is an admin make sure they're slug
     * is in kanso's core configuration
     *
     */
    private function updateSlugs()
    {
        # Get the slugs
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        # Add the slug
        $slugs[] = $this->data['slug'];

        # Make sure the slugs are unique
        $slugs = array_unique(array_values($slugs));

        # If the slug was changed removed the old one
        if ($this->data['slug'] !== $this->slug) {
        	foreach ($slugs as $i => $configSlug) {
	            if ($configSlug === $this->slug) unset($slugs[$i]);
	        }
        }       

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_AUTHOR_SLUGS', array_values($slugs));
    }

    /**
     * Remove the current slug from Kanso's config
     *
     */
    private function removeSlug()
    {
        # Get the slugs
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        # If the slug was changed removed the old one
        foreach ($slugs as $i => $configSlug) {
            if ($configSlug === $this->data['slug']) unset($slugs[$i]);
        }     

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_AUTHOR_SLUGS', array_values($slugs));
    }

}