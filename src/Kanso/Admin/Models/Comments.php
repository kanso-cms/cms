<?php

namespace Kanso\Admin\Models;

/**
 * GET/POST Model for comments page
 *
 * This model is responsible for validating and parsing all
 * GET and POST requests made to the admin panel comments page.
 *
 * The class is instantiated by the respective controller
 */
class Comments
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
     * Parse the $_GET request variables and filter the comments for the requested page.
     *
     * This method parses any URL queries to filter the article listings
     * e.g /admin/comments?status=published&page=3
     * 
     * @return array
     */
	public function parseGet()
	{
        # Prep the response
        $response = [
            'comments'   => $this->loadComments(),
            'max_page'   => 0,
            'queries'    => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        # If the comments are empty,
        # There's no need to check for max pages
        if (!empty($response['comments'])) {
            $response['max_page'] = $this->loadComments(true);
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
        if (!in_array($_post['bulk_action'], ['approved', 'pending', 'spam', 'deleted'])) return false;
        if (!isset($_post['comments']) || !is_array($_post['comments']) || empty($_post['comments'])) return false;
        
        # Get post ids
        $commentIDs = array_filter(array_map('intval', $_post['comments']));

        $this->status($_post['bulk_action'], $commentIDs);
        return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your articles were successfully deleted!'];
    }

    /********************************************************************************
    * PRIVATE HELPERS
    *******************************************************************************/

    /**
     * Check if the  $_GET request queries are empty or set to default
     * 
     * @return boolean
     */
    private function emptyQueries()
    {
        $queries = $this->getQueries();
        return (
            $queries['search'] === false && 
            $queries['page']   === 0 && 
            $queries['sort']   === 'newest' && 
            $queries['status'] === false
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
        if (!isset($queries['sort']))     $queries['sort']     = 'newest';
        if (!isset($queries['status']))   $queries['status']   = false;

        return $queries;
    }

    /**
     * Change comments status
     *
     * @param  string    $status         The comment status to change
     * @param  array     $commentIds     Array of comment ids
     *
     */
    private function status($status, $commentIds) 
    {
        foreach ($commentIds as $id) {
            \Kanso\Kanso::getInstance()->Comments->status($id, $status);
        }
    }

    /**
     * Load comments to display as list
     *
     * @param   boolean    $checkMaxPages    Don't return the comments just check how many pages there are
     * @return  array|int
     *
     */
	private function loadComments($checkMaxPages = false)
	{
		# Get queries
		$queries = \Kanso\Kanso::getInstance()->Request->queries();
        $SQL     = \Kanso\Kanso::getInstance()->Database->Builder();
        $Query   = \Kanso\Kanso::getInstance()->Query();

		# Set defaults
		if (!isset($queries['search']))   $queries['search']   = false;
		if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'newest';
        if (!isset($queries['status']))   $queries['status']   = false;

		# Default operation values
        $page         = ((int)$queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = $queries['sort'] === 'newest' ? 'DESC' : 'ASC' ;
        $sortKey      = 'date';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $search       = $queries['search'];
        $filter       = $queries['status'];
        
        # Filter and sanitize the sort order
        if ($queries['sort'] === 'name')  $sortKey   = 'name';
        if ($queries['sort'] === 'email') $sortKey   = 'email';

        $SQL->SELECT('*')->FROM('comments');
        
         # Filter by status
        if ($filter === 'approved') {
            $SQL->WHERE('status', '=', 'approved');
        }
        if ($filter === 'spam') {
            $SQL->WHERE('status', '=', 'spam');
        }
        if ($filter === 'pending') {
            $SQL->WHERE('status', '=', 'pending');
        }
        if ($filter === 'deleted') {
            $SQL->WHERE('status', '=', 'pending');
        }

        # Is this a search
        if ($search) {
            if (\Kanso\Utility\Str::contains($search, ':')) {
                $keys = explode(':', $search);
                if (in_array($keys[0], ['name', 'email', 'ip_address'])) {
                    $SQL->AND_WHERE($keys[0], 'LIKE', "%$keys[1]%");
                }
            }
            else {
                $SQL->AND_WHERE('content', 'LIKE', "%$search%");

            }
        }
       
        # Set the order
        $SQL->ORDER_BY($sortKey, $sort);

        # Set the limit - Only if we're returning the actual articles
        if (!$checkMaxPages) $SQL->LIMIT($offset, $limit);

        # Find comments
        $comments = $SQL->FIND_ALL();

        # Are we checking the pages ?
        if ($checkMaxPages) return ceil(count($comments) / $perPage);

        # Append custom keys
        foreach ($comments as $key => $comment) {
            $comments[$key]['permalink'] = $Query->the_permalink($comment['post_id']);
            $comments[$key]['title']     = $Query->the_title($comment['post_id']);
            $comments[$key]['avatar']    = $Query->get_gravatar($comment['email'], 100, true);
        }

        return $comments;
	}
}