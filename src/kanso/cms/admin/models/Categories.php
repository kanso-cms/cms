<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\cms\admin\models\BaseModel;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;

/**
 * Categories model
 *
 * @author Joe J. Howard
 */
class Categories extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn)
        {
            return $this->parseGet();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        if ($this->isLoggedIn)
        {
            return $this->parsePost();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        return false;
    }

   /**
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        $response = [
            'categories'    => $this->loadCategories(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        if (!empty($response['categories']))
        {
            $response['max_page'] = $this->loadCategories(true);
        }

        return $response;
    }

    /**
     * Parse and validate the POST request from any submitted forms
     * 
     * @access private
     * @return array|false
     */
    public function parsePost()
    {
        if (!$this->validatePost())
        {
            return false;
        }

        $catIds = array_filter(array_map('intval', $this->post['categories']));

        if (!empty($catIds))
        {
            if ($this->post['bulk_action'] === 'delete')
            {
                $this->delete($catIds);

                return $this->postMessage('success', 'Your categories were successfully deleted!');
            }
            if ($this->post['bulk_action'] === 'clear')
            {
                $this->clear($catIds);
                
                return $this->postMessage('success', 'Your categories were successfully cleared!');
            }
            if ($this->post['bulk_action'] === 'update')
            {
                $update = $this->update(intval($catIds[0]));

                if ($update === 'name_exists')
                {
                    return $this->postMessage('warning', 'Could not update category. Another category with the same name already exists.');
                }

                if ($update === 'slug_exists')
                {
                    return $this->postMessage('warning', 'Could not update category. Another category with the same slug already exists.');
                }
                
                return $this->postMessage('success', 'Category was successfully updated!');
            }
        }

        return false;        
    }

     /**
     * Validates all POST variables are set
     * 
     * @access private
     * @return bool
     */
    private function validatePost(): bool
    {        
        # Validation
        if (!isset($this->post['bulk_action']) || empty($this->post['bulk_action']))
        {
            return false;
        }

        if (!in_array($this->post['bulk_action'], ['clear', 'delete', 'update']))
        {
            return false;
        }

        if (!isset($this->post['categories']) || !is_array($this->post['categories']) || empty($this->post['categories']))
        {
            return false;
        }

        return true;
    }

     /**
     * Updates a category
     *
     * @access private
     * @param  int     $id Single category id
     * @return bool|string
     */
    private function update(int $id)
    {
        if ( !isset($this->post['name']) || !isset($this->post['slug']) || !isset($this->post['description']))
        {
            return false;
        }

        $name        = trim($this->post['name']);
        $slug        = Str::slug($this->post['slug']);
        $description = trim($this->post['description']);
        $category    = $this->CategoryManager->byId($id);

        if (!$category)
        {
            return false;
        }

        # Validate category with same name does not already exist
        $existsName = $this->CategoryManager->byName($name);

        if ($existsName && $existsName->id !== $id)
        {
            return 'name_exists';
        }

        # Validate category with same slug does not already exist
        $existsSlug = $this->CategoryManager->bySlug($slug);

        if ($existsSlug && $existsSlug->id !== $id)
        {
            return 'slug_exists';
        }

        $category->name = $name;
        $category->slug = $slug;
        $category->description = $description;
        $category->save();

        return true;
    }

    /**
     * Delete articles by id
     *
     * @access private
     * @param  array   $ids List of post ids
     * @return bool
     */
    private function delete(array $ids)
    {
        foreach ($ids as $id)
        {
            $category = $this->CategoryManager->byId($id);

            if ($category)
            {
                $category->delete();
            }
        }
    }

    /**
     * Clear categories of articles
     *
     * @access private
     * @param  array   $ids List of post ids
     * @return bool
     */
    private function clear(array $ids)
    {
        foreach ($ids as $id)
        {
            $category = $this->CategoryManager->byId($id);

            if ($category)
            {
                $category->clear();
            }
        }
    }

    /**
     * Check if the GET URL queries are either empty or set to defaults
     *
     * @access private
     * @return bool
     */
    private function emptyQueries(): bool
    {
        $queries = $this->getQueries();
        
        return (
            $queries['search'] === false && 
            $queries['page']   === 0 && 
            $queries['sort']   === 'name'
        );
    }

    /**
     * Returns the requested GET queries with defaults
     *
     * @access private
     * @return array
     */
    private function getQueries(): array
    {
        # Get queries
        $queries = $this->Request->queries();

        # Set defaults
        if (!isset($queries['search'])) $queries['search'] = false;
        if (!isset($queries['page']))   $queries['page']   = 0;
        if (!isset($queries['sort']))   $queries['sort']   = 'name';

        return $queries;
    }

    /**
     * Returns the list of categories for display
     *
     * @access private
     * @param  bool $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadCategories(bool $checkMaxPages = false)
    {
        $queries = $this->getQueries();
 # Default operation values
        $page         = ((int)$queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'name';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $search       = $queries['search'];

        # Select the posts
        $this->SQL->SELECT('id')->FROM('categories');

        # Set the limit - Only if we're returning the actual categories list
        # and not sorting by article count
        if (!$checkMaxPages && $queries['sort'] === 'name')
        {
            $this->SQL->LIMIT($offset, $limit);
            $this->SQL->ORDER_BY($sortKey, $sort);
        }
        
        # Search the name
        if ($search)
        {
            $this->SQL->AND_WHERE('name', 'like', '%'.$queries['search'].'%');
        }

        # Find the articles
        $rows = $this->SQL->FIND_ALL();

        # Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($rows) / $perPage);
        }

        $result = [];

        foreach ($rows as $row)
        {
            $this->SQL->SELECT('id')->FROM('posts')->WHERE('category_id', '=', $row['id']);

            $category = $this->CategoryManager->byId($row['id']);
            
            $category->article_count = count($this->SQL->FIND_ALL());

            $result[] = $category;
        }

        # If we're sorting by article count, we need to paginate
        # all the results and return the requested page of categories
        if ($queries['sort'] !== 'name' && !$checkMaxPages)
        {
            $result = Arr::sortMulti($result, 'article_count');
            
            $result = Arr::paginate($result, $page, $perPage);

            if (isset($result[0]))
            {
                return $result[0];
            }
            
            return [];
        }

        return $result;
    }
}
