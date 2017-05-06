<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers\Providers;

use Kanso\CMS\Wrappers\User;
use Kanso\CMS\Wrappers\Providers\Provider;

/**
 * User provider
 *
 * @author Joe J. Howard
 */
class UserProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $user = new User($this->SQL, $row);

        if ($user->save())
        {
            return $user;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function byId(int $id)
    {
    	return $this->byKey('id', $id, true);
    }

    /**
     * {@inheritdoc}
     */
    public function byKey(string $key, $value, bool $single = false)
    {
    	if ($single)
        {
    		$row = $this->SQL->SELECT('*')->FROM('users')->WHERE($key, '=', $value)->ROW();

    		if ($row)
            {
                return new User($this->SQL, $row);
            }

            return null;
    	}
    	else
        {
            $users = [];

    		$rows = $this->SQL->SELECT('*')->FROM('users')->WHERE($key, '=', $value)->FIND_ALL();

    		foreach ($rows as $row)
            {
                $users[] = new User($this->SQL, $row);
            }

            return $users;
    	}
    }
}
