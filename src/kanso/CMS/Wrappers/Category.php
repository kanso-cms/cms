<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers;

use InvalidArgumentException;
use Kanso\CMS\Wrappers\Wrapper;
use Kanso\Framework\Utility\Str;

/**
 * Category utility wrapper
 *
 * @author Joe J. Howard
 */
class Category extends Wrapper
{
    /**
     * {@inheritdoc}
     */
    public function __set(string $key, $value)
    {
        $this->data[$key] = $key === 'slug' ? Str::slug($value) : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool
	{
        $saved = false;

        $catExists = $this->SQL->SELECT('*')->FROM('categories')->WHERE('slug', '=', $this->data['slug'])->ROW();

        if ($catExists)
        {
            $saved = $this->SQL->UPDATE('categories')->SET($this->data)->WHERE('id', '=', $catExists['id'])->QUERY();
        }
        else
        {
            $saved = $this->SQL->INSERT_INTO('categories')->VALUES($this->data)->QUERY();
            
            if ($saved)
            {
                $this->data['id'] = intval($this->SQL->connection()->lastInsertId());
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
                throw new InvalidArgumentException(vsprintf("%s(): The 'uncategorized' taxonomy is not deletable.", [__METHOD__]));
            }

            $this->clear();
            {
                return $this->SQL->DELETE_FROM('categories')->WHERE('id', '=', $this->data['id'])->QUERY() ? true : false;
            }
        }

        return false;
	}

    /**
     * Clears all posts from the category
     *
     * @access public
     * @return bool
     * @throws \InvalidArgumentException If this is category id 1
     */
    public function clear(): bool
    {
        if (isset($this->data['id']))
        {
            if ($this->data['id'] === 1)
            {
                throw new InvalidArgumentException(vsprintf("%s(): The 'uncategorized' taxonomy cannot be cleared.", [__METHOD__]));
            }

            $this->SQL->UPDATE('posts')->SET(['category_id' => 1])->WHERE('category_id', '=', $this->data['id'])->QUERY();

            return true;
        }

        return false;
    }
}
