<?php

namespace Kanso\Admin\Models;

/**
 * GET/POST Model for comment users page
 *
 * This model is responsible for validating and parsing all
 * GET and POST requests made to the admin panel comment users page.
 *
 * The class is instantiated by the respective controller
 */
class CommentUsers
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
     * Parse the $_GET request variables and filter the comment users for the requested page.
     *
     * This method parses any URL queries to filter the article listings
     * e.g /admin/comment-users?status=published&page=3
     * 
     * @return array
     */
	public function parseGet()
	{
        # Prep the response
        $response = [
            'commenters'    => $this->loadUsers(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        # If the commenters are empty,
        # There's no need to check for max pages
        if (!empty($response['users'])) {
            $response['max_page'] = $this->loadUsers(true);
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
        if (!in_array($_post['bulk_action'], ['whitelist', 'blacklist', 'nolist'])) return false;
        if (!isset($_post['users']) || !is_array($_post['users']) || empty($_post['users'])) return false;
        
        # Get post ids
        $ipAddresses = array_filter($_post['users']);

        $this->moderate($ipAddresses, $_post['bulk_action']);

        return ['class' => 'success', 'icon' => 'check', 'msg' => 'Users were successfully moderated!'];
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
     * Moderate a list of ip addresses 
     *
     * @param  array     $ips       Array of ip addresses
     * @param  string    $status    The status to set blacklist|whitelist|nolist
     *
     */
    private function moderate($ips, $status)
    {
        foreach ($ips as $ip) {
            \Kanso\Comments\CommentManager::moderateIp($ip, $status);
        }
    }

    /**
     * Load comment users to display as list
     *
     * @param   boolean    $checkMaxPages    Don't return the comment users just check how many pages there are
     * @return  array|int
     *
     */
	private function loadUsers($checkMaxPages = false)
	{
		# Get queries
		$queries = \Kanso\Kanso::getInstance()->Request->queries();
        $SQL     = \Kanso\Kanso::getInstance()->Database->Builder();

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

        # Is this a search
        if ($search) {
            if (\Kanso\Utility\Str::contains($search, ':')) {
                $keys = explode(':', $search);
                if (in_array($keys[0], ['name', 'email', 'ip_address'])) {
                    $SQL->AND_WHERE($keys[0], 'LIKE', "%$keys[1]%");
                }
            }
        }
       
        # Set the order
        $SQL->ORDER_BY($sortKey, $sort);

        # Find comments
        $comments = $SQL->FIND_ALL();

        # Black and whitelisted users
        $blacklistedIps = \Kanso\Comments\Spam\SpamProtector::loadDictionary('blacklist_ip');
        $whiteListedIps = \Kanso\Comments\Spam\SpamProtector::loadDictionary('whitelist_ip');

        # Create a list of users
        $users = [];
        foreach ($comments as $comment) {

            if ($filter === 'whitelist') {
                if (!in_array($comment['ip_address'], $whiteListedIps)) continue;
            }
            else if ($filter === 'blacklist') {
                if (!in_array($comment['ip_address'], $blacklistedIps)) continue;
            }

            if (!isset($users[$comment['ip_address']])) {
                $users[$comment['ip_address']] = [
                    'reputation'   => $comment['rating'],
                    'posted_count' => 1,
                    'spam_count'   => $comment['status'] === 'spam' ? 1 : 0,
                    'first_date'   => $comment['date'],
                    'blacklisted'  => in_array($comment['ip_address'], $blacklistedIps),
                    'whitelisted'  => in_array($comment['ip_address'], $whiteListedIps),
                    'ip_address'   => $comment['ip_address'],
                    'name'         => $comment['name'],
                    'email'        => $comment['email'],
                    'avatar'       => \Kanso\Kanso::getInstance()->Query->get_avatar($comment['email'], 150, true),
                ];
            }
            else {
                $users[$comment['ip_address']]['reputation'] += $comment['rating'];
                $users[$comment['ip_address']]['posted_count'] += 1;
                if ($comment['status'] === 'spam') $users[$comment['ip_address']]['spam_count'] += 1;
                if ($comment['date'] < $users[$comment['ip_address']]['first_date']) $users[$comment['ip_address']]['first_date'] = $comment['date'];
            }
        }

        # Are we checking the pages ?
        if ($checkMaxPages) return ceil(count($users) / $perPage);

        # Append custom keys
        $paged = \Kanso\Utility\Arr::paginate($users, $page, $perPage);
        if (isset($paged[$page])) return $paged[$page];
        
        return [];
	}


}