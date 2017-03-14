<?php

namespace Kanso\Admin\Models;

/**
 * GET/POST Model for Categories page
 *
 * This model is responsible for validating and parsing all
 * GET and POST requests made to the admin panel categories page.
 *
 * The class is instantiated by the respective controller
 */
class Categories
{

    /********************************************************************************
    * PUBLIC INITIALIZATION
    *******************************************************************************/

    /**
     * Empty Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Parse the $_GET request variables and filter the categories for the requested page.
     *
     * This method parses any URL queries to filter the category listings
     * e.g /admin/categories?status=published&page=3
     * 
     * @return array
     */
    public function parseGet()
    {
        # Prep the response
        $response = [
            'cats'          => $this->loadCats(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        # If the articles are empty,
        # There's no need to check for max pages
        if (!empty($response['cats'])) {
            $response['max_page'] = $this->loadCats(true);
        }

        return $response;
    }

    /**
     * Parse and validate the $_POST request variables from any submitted forms
     * 
     * @return array
     */
    public function parsePost()
    {
        # Get the POST variables
        $_post = \Kanso\Kanso::getInstance()->Request->fetch();

        # Validation
        if (!isset($_post['bulk_action']) || empty($_post['bulk_action'])) return false;
        if (!in_array($_post['bulk_action'], ['clear', 'delete'])) return false;
        if (!isset($_post['cats']) || !is_array($_post['cats']) || empty($_post['cats'])) return false;
        
        # Get post ids
        $categoryIds = array_filter(array_map('intval', $_post['cats']));

        # Dispatch
        $response = false;

        if ($_post['bulk_action'] === 'delete') {
            $this->delete($categoryIds);
            return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your categories were successfully deleted!'];
        }
        else {
            $this->clear($categoryIds);
            return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your categories were successfully updated!'];
        }

        return false;  
    }

    /********************************************************************************
    * PRIVATE HELPERS
    *******************************************************************************/

    /**
     * Check if the $_GET request queries are empty or set to default
     * 
     * @return boolean
     */
    private function emptyQueries()
    {
        $queries = $this->getQueries();
        return (
            $queries['search'] === false && 
            $queries['page']   === 0 && 
            $queries['sort']   === 'name'
        );
    }

    /**
     * Get the Queries from the URL with defaults
     * if any are missing
     * 
     * @return array
     */
    private function getQueries()
    {
        # Get queries
        $queries = \Kanso\Kanso::getInstance()->Request->queries();

        # Set defaults
        if (!isset($queries['search']))   $queries['search']   = false;
        if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'name';

        return $queries;
    }

   
    /**
     * Clear category of articles
     *
     * @param  array    $categoryIds      Array of category ids
     *
     */
    private function clear($categoryIds) 
    {
        foreach ($categoryIds as $id) {
            \Kanso\Kanso::getInstance()->Bookkeeper->clearTaxonomy($id, 'category');
        }
    }

    /**
     * Delete a category
     *
     * @param  array    $categoryIds      Array of category ids
     *
     */
    private function delete($categoryIds) 
    {
        foreach ($categoryIds as $id) {
            \Kanso\Kanso::getInstance()->Bookkeeper->clearTaxonomy($id, 'category', true);
        }
    }

    /**
     * Load categories to display as list
     *
     * @param   boolean    $checkMaxPages    Don't return the categories just check how many pages there are
     * @return  array|int
     *
     */
    private function loadCats($checkMaxPages = false)
    {
        # Get queries
        $queries = \Kanso\Kanso::getInstance()->Request->queries();

        # Set defaults
        if (!isset($queries['search']))   $queries['search']   = false;
        if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'name';

        # Default operation values
        $page         = ((int)$queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'name';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $search       = $queries['search'];

        # Get the Kanso Query builder
        $Query = \Kanso\Kanso::getInstance()->Query;

        # Get the Kanso SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        # Select the posts
        $SQL->SELECT('*')->FROM('categories');

        # Set the limit - Only if we're returning the actual tag list
        # and not sorting by article count
        if (!$checkMaxPages && $queries['sort'] === 'name') {
            $SQL->LIMIT($offset, $limit);
            $SQL->ORDER_BY($sortKey, $sort);
        }
        
        # Search the name
        if ($search) {
            $SQL->AND_WHERE('name', 'like', '%'.$queries['search'].'%');
        }

        # Find the articles
        $cats = $SQL->FIND_ALL();

        # Add all the article count
        foreach ($cats as $i => $cat) {
            $SQL->SELECT('id')->FROM('posts');
            $SQL->WHERE('category_id', '=', $cat['id']);
            $cats[$i]['article_count'] = count($SQL->FIND_ALL());
            $cats[$i]['permalink']     = \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/'.$cat['slug'];
           
        }

        # If we're sorting by article count
        # we still need to sort the array and return only the page
        if ($queries['sort'] !== 'name' && !$checkMaxPages) {
            $cats = \Kanso\Utility\Arr::sortMulti($cats, 'article_count');
            $cats = \Kanso\Utility\Arr::paginate($cats, $page, $perPage);
            if (isset($cats[0])) return $cats[0];
            return [];
        }

        # Are we checking the pages ?
        if ($checkMaxPages) return ceil(count($cats) / $perPage);

        return $cats;
    }


}