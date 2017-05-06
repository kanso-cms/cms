<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers\Providers;

use Kanso\CMS\Wrappers\Tag;
use Kanso\CMS\Wrappers\Providers\Provider;

/**
 * Tag provider
 *
 * @author Joe J. Howard
 */
class TagProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $tag = new Tag($this->SQL, $row);

        if ($tag->save())
        {
            return $tag;
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
    		$row = $this->SQL->SELECT('*')->FROM('tags')->WHERE($key, '=', $value)->ROW();

    		if ($row)
            {
                return new Tag($this->SQL, $row);
            }

            return null;
    	}
    	else
        {
            $tags = [];

    		$rows = $this->SQL->SELECT('*')->FROM('tags')->WHERE($key, '=', $value)->FIND_ALL();

    		foreach ($rows as $row)
            {
                $tags[] = new Tag($this->SQL, $row);
            }

            return $tags;
    	}
    }
}
