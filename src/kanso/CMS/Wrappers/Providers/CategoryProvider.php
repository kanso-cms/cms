<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers\Providers;

use Kanso\CMS\Wrappers\Category;
use Kanso\CMS\Wrappers\Providers\Provider;

/**
 * Category provider
 *
 * @author Joe J. Howard
 */
class CategoryProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $category = new Category($this->SQL, $row);

        if ($category->save())
        {            
            return $category;
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
    		$row = $this->SQL->SELECT('*')->FROM('categories')->WHERE($key, '=', $value)->ROW();

    		if ($row)
            {
                return new Category($this->SQL, $row);
            }

            return null;
    	}
    	else
        {
            $categories = [];

    		$rows = $this->SQL->SELECT('*')->FROM('categories')->WHERE($key, '=', $value)->FIND_ALL();

    		foreach ($rows as $row)
            {
                $categories[] = new Category($this->SQL, $row);
            }

            return $categories;
    	}
    }
}
