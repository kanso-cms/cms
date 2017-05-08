<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\providers;

use kanso\cms\wrappers\Comment;
use kanso\cms\wrappers\providers\Provider;

/**
 * Comment provider
 *
 * @author Joe J. Howard
 */
class CommentProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $comment = new Comment($this->SQL, $row);

        if ($comment->save())
        {
            return $comment;
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
    		$row = $this->SQL->SELECT('*')->FROM('comments')->WHERE($key, '=', $value)->ROW();

    		if ($row)
            {
                return new Comment($this->SQL, $row);
            }

            return null;
    	}
    	else
        {
            $comments = [];

    		$rows = $this->SQL->SELECT('*')->FROM('comments')->WHERE($key, '=', $value)->FIND_ALL();

    		foreach ($rows as $row)
            {
                $comments[] = new Comment($this->SQL, $row);
            }

            return $comments;
    	}
    }
}
