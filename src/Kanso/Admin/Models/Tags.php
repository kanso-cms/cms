<?php

namespace Kanso\Admin\Models;

/**
 * GET/POST Model for Tags page
 *
 * This model is responsible for validating and parsing all
 * GET and POST requests made to the admin panel tags page.
 *
 * The class is instantiated by the respective controller
 */
class Tags
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
     * Parse the $_GET request variables and filter the tags for the requested page.
     *
     * This method parses any URL queries to filter the tag listings
     * e.g /admin/tags?status=published&page=3
     * 
     * @return array
     */
	public function parseGet()
	{
        # Prep the response
        $response = [
            'tags'       => $this->loadTags(),
            'max_page'   => 0,
            'queries'    => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),

        ];

        # If the articles are empty,
        # There's no need to check for max pages
        if (!empty($response['tags'])) {
            $response['max_page'] = $this->loadTags(true);
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
        if (!isset($_post['tags']) || !is_array($_post['tags']) || empty($_post['tags'])) return false;
        
        # Get post ids
        $tagIds = array_filter(array_map('intval', $_post['tags']));

        # Dispatch
        $response = false;

        if ($_post['bulk_action'] === 'delete') {
            $this->delete($tagIds);
            return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your tags were successfully deleted!'];
        }
        else {
            $this->clear($tagIds);
            return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your tags were successfully updated!'];
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
     * Clear tag of articles
     *
     * @param  array    $tagIds      Array of tag ids
     *
     */
    private function clear($tagIds) 
    {
        foreach ($tagIds as $id) {
            \Kanso\Kanso::getInstance()->Bookkeeper->clearTaxonomy($id, 'tag');
        }
    }

    /**
     * Delete a tag
     *
     * @param  array    $tagIds      Array of tag ids
     *
     */
    private function delete($tagIds) 
    {
        foreach ($tagIds as $id) {
            \Kanso\Kanso::getInstance()->Bookkeeper->clearTaxonomy($id, 'tag', true);
        }
    }

    /**
     * Load tags to display as list
     *
     * @param   boolean    $checkMaxPages    Don't return the tags just check how many pages there are
     * @return  array|int
     *
     */
	private function loadTags($checkMaxPages = false)
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
        $SQL->SELECT('*')->FROM('tags');

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
        $tags = $SQL->FIND_ALL();

        # Add all the article count
        foreach ($tags as $i => $tag) {
            $SQL->SELECT('posts.id')->FROM('posts');
            $SQL->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');
            $SQL->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');
            $SQL->AND_WHERE('tags.id', '=', $tag['id']);
            $tags[$i]['article_count'] = count($SQL->FIND_ALL());
            $tags[$i]['permalink']     = \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/tags/'.$tag['slug'];
           
        }

        # If we're sorting by article count
        # we still need to sort the array and return only the page
        if ($queries['sort'] !== 'name' && !$checkMaxPages) {
            $tags = \Kanso\Utility\Arr::sortMulti($tags, 'article_count');
            $tags = \Kanso\Utility\Arr::paginate($tags, $page, $perPage);
            if (isset($tags[0])) return $tags[0];
            return [];
        }

        # Are we checking the pages ?
        if ($checkMaxPages) return ceil(count($tags) / $perPage);

        return $tags;
	}

}