<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use InvalidArgumentException;
use kanso\framework\utility\Str;
use kanso\framework\utility\UUID;

/**
 * User utility wrapper.
 *
 * @author Joe J. Howard
 */
class User extends Wrapper
{
    /**
     * Override the set method.
     *
     * @param string $key   Key to set
     * @param mixed  $value Value to set
     */
    public function __set(string $key, $value): void
    {
        if ($key === 'slug')
        {
            $this->data[$key] = Str::slug($value);
        }
        elseif ($key === 'username')
        {
            $this->data[$key] = Str::alphaNumDash($value);
        }
        else
        {
            $this->data[$key] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool
	{
        $saved = false;

        if (isset($this->data['id']))
        {
            $saved = $this->SQL->UPDATE('users')->SET($this->data)->WHERE('id', '=', $this->data['id'])->QUERY();
        }
        else
        {
            if (!isset($this->data['access_token']) || empty($this->data['access_token']))
            {
                $this->generateAccessToken();
            }

            $saved = $this->SQL->INSERT_INTO('users')->VALUES($this->data)->QUERY();

            if ($saved)
            {
                $this->data['id'] = intval($this->SQL->connectionHandler()->lastInsertId());
            }
        }

        return !$saved ? false : true;
	}

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
	{
        if (isset($this->data['id']))
        {
            if ($this->data['id'] === 1)
            {
                throw new InvalidArgumentException(vsprintf("%s(): The primary user with id '1' is not deletable.", [__METHOD__]));
            }

            if ($this->SQL->DELETE_FROM('users')->WHERE('id', '=', $this->data['id'])->QUERY())
            {
                // Change all their posts
                $this->SQL->UPDATE('posts')->SET(['author_id' => 1])->WHERE('author_id', '=', $this->data['id'])->QUERY();

                // Change all their uploaded images
                $this->SQL->UPDATE('media_uploads')->SET(['uploader_id' => 1])->WHERE('uploader_id', '=', $this->data['id'])->QUERY();

                return true;
            }
        }

        return false;
	}

    /**
     * Generate an access token for this user.
     *
     * @return \kanso\cms\wrappers\User
     */
    public function generateAccessToken(): User
    {
        $this->data['access_token'] = UUID::v4();

        return $this;
    }
}
