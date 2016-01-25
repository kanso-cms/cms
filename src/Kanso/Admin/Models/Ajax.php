<?php

namespace Kanso\Admin\Models;

/**
 * Admin User Manager
 *
 * This class has as a number of utility helper functions
 * for managing users from within the admin panel.
 *
 */
class Ajax
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Get the articles for listing in the admin panel
     *
     * This function updates the user's administrator settings.
     * i.e username, email and password.
     *
     * @param  $queries       array    POST data from client
     * @return array
     */
    public function getAllArticles($queries)
    {
         # Default operation values
        $isSearch     = $queries['search'] !== 'false';
        $searchValue  = false;
        $searchKey    = false;
        $leftJoin     = false;
        $page         = ((int)$queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'posts.created';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        
        # If this is a search, clean and santize the search keys
        if ($isSearch) {
            
            $searchValue = $queries['search'];

            $validKeys   = [
                'title'    => 'title',
                'author'   => 'author.name',
                'type'     => 'type',
                'status'   => 'status',
                'category' => 'category.name',
                'tags'     => 'tags.name',
            ];

            # Validate if the search is specific to a column
            if (\Kanso\Utility\Str::contains($searchValue, ':')) {
                
                $value    = trim(\Kanso\Utility\Str::getAfterFirstChar($searchValue, ':'));
                $key      = trim(\Kanso\Utility\Str::getBeforeFirstChar($searchValue, ':'));
                $key      = isset($validKeys[$key]) ? $validKeys[$key] : false;

                # Split comma seperated list of values into a search array
                if ($key) {
                    $searchKey   = $key;
                    $searchValue = [$value];
                    if (\Kanso\Utility\Str::contains($searchValue[0], ' ')) $searchValue = array_filter(array_map('trim', explode(' ', $searchValue[0])));
                }
                else {
                    // Key doesnt exist
                    return [];
                }
            }

        }

        # Filter and sanitize the sort order
        if ($queries['sortBy'] === 'newest' || $queries['sortBy'] === 'published') $sort = 'DESC';
        if ($queries['sortBy'] === 'oldest' || $queries['sortBy'] === 'drafts') $sort = 'ASC';

        if ($queries['sortBy'] === 'category')  $sortKey   = 'categories.name';
        if ($queries['sortBy'] === 'tags')      $sortKey   = 'tags.name';
        if ($queries['sortBy'] === 'drafts')    $sortKey   = 'posts.status';
        if ($queries['sortBy'] === 'published') $sortKey   = 'posts.status';
        if ($queries['sortBy'] === 'type')      $sortKey   = 'posts.type';
        if ($queries['sortBy'] === 'title')     $sortKey   = 'posts.title';

        # Get the Kanso Query builder
        $Query = \Kanso\Kanso::getInstance()->Query;

        # Get the Kanso SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        # Select the posts
        $SQL->SELECT('posts.*')->FROM('posts');

        # Set the limit
        $SQL->LIMIT($offset, $limit);

        # Set the order
        $SQL->ORDER_BY($sortKey, $sort);

        # Apply basic joins for queries
        $SQL->LEFT_JOIN_ON('users', 'users.id = posts.author_id');
        $SQL->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id');
        $SQL->LEFT_JOIN_ON('categories', 'posts.category_id = categories.id');
        $SQL->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');
        $SQL->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');
        $SQL->GROUP_BY('posts.id');

        # Find the articles
        $articles = $SQL->FIND_ALL();

        # Pre validate there are actually some articles to process
        if (empty($articles)) return [];

        # Add full joins as keys
        foreach ($articles as $i => $row) {
            $articles[$i]['tags']     = $SQL->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('tags_to_posts.post_id', '=', (int)$row['id'])->FIND_ALL();
            $articles[$i]['category'] = $SQL->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$row['category_id'])->FIND();
            $articles[$i]['author']   = $SQL->SELECT('*')->FROM('users')->WHERE('id', '=', (int)$row['author_id'])->FIND();
            $articles[$i]['excerpt']  = urldecode($row['excerpt']);
        }

        # Loop and filter the articles
        foreach ($articles as $i => $article) {

            // Search the article
            if ($isSearch && $searchKey && is_array($searchValue)) {
                foreach ($searchValue as $query) {
                    if (isset($article[$searchKey])) {
                        if (!preg_match($article[$searchKey], "%$query%")) unset($articles[$i]);
                    }
                }
            }

            // Search the 'content' key using regex match
            else if ($isSearch && !$searchKey && $searchValue) {
                if (!preg_match($article['excerpt'], "%$searchValue%") || !preg_match($article['title'], "%$searchValue%")) unset($articles[$i]);
            }

            if ( $article['status'] === 'draft') {
                $articles[$i]['permalink'] = rtrim($Query->the_permalink($article['id']), '/').'?draft';
            }
            else {
                $articles[$i]['permalink'] = $Query->the_permalink($article['id']);
            }

            $articles[$i]['edit_permalink'] = '/admin/write/'.$Query->the_slug($article['id']);

            $articles[$i]['category']['permalink'] = $Query->the_category_url($articles[$i]['category_id']);


            foreach ($article['tags'] as $t => $tag) {
                $articles[$i]['tags'][$t]['permalink'] = $Query->the_tag_url($tag['id']);
            }
            
            $articles[$i]['author']['permalink'] = $Query->the_author_url($article['author_id']);

        }

        # Pageinate the articles
        return [$articles];
    }

    /**
     * Get the taxonomies for listing in the admin panel
     *
     * @param  $queries       array    POST data from client
     * @return array
     */
    public function getAllTaxonomies($queries)
    {

        # Get the Kanso Query object
        $Query = \Kanso\Kanso::getInstance()->Query;

        # Get the SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        $categories   = $SQL->SELECT('*')->FROM('categories')->FIND_ALL();
        $tags         = $SQL->SELECT('*')->FROM('tags')->FIND_ALL();

        $isSearch     = $queries['search'] !== 'false';
        $searchValue  = false;
        $searchKey    = false;
        $page         = ((int)$queries['page']) -1;
        $sort         = 'DESC';
        $sortKey      = isset($queries['sortBy']) ? $queries['sortBy'] : 'name';

        foreach ($tags as $i => $tag) {
            $tags[$i]['permalink'] = $Query->the_tag_url($tag['id']);
            $tagPosts = $SQL->SELECT('posts.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('posts', 'tags_to_posts.post_id = posts.id')->WHERE('tags_to_posts.tag_id', '=', (int)$tag['id'])->FIND_ALL();
            $tags[$i]['posts'] = [];
            foreach ($tagPosts as $post) {
                $tags[$i]['posts'][] = [
                    'name'      => $post['title'],
                    'permalink' => $Query->the_permalink($post['id']),
                ];
            }
            $tags[$i]['type'] = 'tag';
        }

        foreach ($categories as $i => $category) {
            $categories[$i]['permalink'] = $Query->the_category_url($category['id']);
            $categoryPosts = $SQL->SELECT('*')->FROM('posts')->WHERE('category_id', '=', (int)$category['id'])->FIND_ALL();
            $categories[$i]['posts'] = [];
            foreach ($categoryPosts as $post) {
                $categories[$i]['posts'][] = [
                    'name'      => $post['title'],
                    'permalink' => $Query->the_permalink($post['id']),
                ];
            }
            $categories[$i]['type'] = 'category';
        }

        $list = array_merge($tags, $categories);

        if ($isSearch) {
            
            $searchValue = $queries['search'];

            $validKeys   = [
                'name'     => 'name',
                'type'     => 'type',
            ];

            if (\Kanso\Utility\Str::contains($searchValue, ':')) {
                
                $value    = trim(\Kanso\Utility\Str::getAfterFirstChar($searchValue, ':'));
                $key      = trim(\Kanso\Utility\Str::getBeforeFirstChar($searchValue, ':'));
                $key      = isset($validKeys[$key]) ? $validKeys[$key] : false;
                if ($key) {
                    $searchKey   = $key;
                    $searchValue = $value;
                    
                }
                else {
                    // Key doesnt exist
                    return [];
                }
            }

        }

        // Search a table with an array of key/value matches
        if ($isSearch && $searchKey && $searchValue) {
            foreach ($list as $i => $item) {
                if (is_string($item[$searchKey]) && strtolower($item[$searchKey]) !== strtolower($searchValue)) {
                    unset($list[$i]);
                }
            }
        }
        else if ($isSearch && !$searchKey && $searchValue) {
            foreach ($list as $i => $item) {
                if (strtolower($item['type']) === strtolower($searchValue) || strtolower($item['name']) === strtolower($searchValue)) {
                    continue;
                }
                else {
                    unset($list[$i]);
                }
            }
        }

        $list = \Kanso\Utility\Arr::sortMulti($list, $sortKey); 

        return  \Kanso\Utility\Arr::paginate($list, $page, 10);

    }

    /**
     * Get the comments for listing in the admin panel
     *
     * @param  $queries    array     POST data from client
     * @param  $filter     string
     * @return array
     */
    public function loadAllComments($queries, $filter)
    {

        # Get the Kanso Query object
        $Query = \Kanso\Kanso::getInstance()->Query();

        # Get the SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();
        
        $isSearch = $queries['search'] !== 'false';
        $page     = ((int)$queries['page']) -1;
        $comments = [];
        $sort     = $queries['sortBy'] === 'newest' ? 'DESC' : 'ASC' ;

        if ($isSearch) {

            $validKeys = [
                'ip'     => 'ip_address',
                'status' => 'status',
                'user'   => 'name',
                'email'  => 'email',
            ];

            $searchValue = $queries['search'];
            $searchKey   = false;

            if (\Kanso\Utility\Str::contains($searchValue, ':')) {
                
                $value    = \Kanso\Utility\Str::getAfterFirstChar($searchValue, ':');
                $key      = \Kanso\Utility\Str::getBeforeFirstChar($searchValue, ':');
                $key      = isset($validKeys[$key]) ? $validKeys[$key] : false;
                if ($key) {
                    $searchKey   = $key;
                    $searchValue = $value; 
                }
            }

            if ($searchKey) {
                $comments = $SQL->SELECT('*')->FROM('comments')->WHERE($searchKey, '=', $searchValue);
            }
            else {
                $comments = $SQL->SELECT('*')->FROM('comments')->WHERE('content', 'LIKE', "%$searchValue%");
            }

            if ($filter === 'all') {
                $comments = $comments->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'approved') {
                $comments = $comments->WHERE('status', '=', 'approved')->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'spam') {
                $comments = $comments->WHERE('status', '=', 'spam')->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'pending') {
                $comments = $comments->WHERE('status', '=', 'pending')->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'deleted') {
                $comments = $comments->WHERE('status', '=', 'deleted')->ORDER_BY('date', $sort)->FIND_ALL();
            }

        }
        else {
            if ($filter === 'all') {
                $comments = $SQL->SELECT('*')->FROM('comments')->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'approved') {
                $comments = $SQL->SELECT('*')->FROM('comments')->WHERE('status', '=', 'approved')->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'spam') {
                $comments = $SQL->SELECT('*')->FROM('comments')->WHERE('status', '=', 'spam')->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'pending') {
                $comments = $SQL->SELECT('*')->FROM('comments')->WHERE('status', '=', 'pending')->ORDER_BY('date', $sort)->FIND_ALL();
            }
            if ($filter === 'deleted') {
                $comments = $SQL->SELECT('*')->FROM('comments')->WHERE('status', '=', 'deleted')->ORDER_BY('date', $sort)->FIND_ALL();
            }
        }

        foreach ($comments as $key => $comment) {
            $comments[$key]['permalink'] = $Query->the_permalink($comment['post_id']);
            $comments[$key]['title']     = $Query->the_title($comment['post_id']);
            $comments[$key]['avatar']    = $Query->get_avatar($comment['email'], 100, true);
        }

        $comments = \Kanso\Utility\Arr::paginate($comments, $page, 10);
        
        return $comments;
    }


    /**
     * Get a single info on the comment
     *
     * @param  $queries    array     POST data from client
     * @param  $filter     string
     * @return array
     */
    public function getCommentInfo($queries)
    {

        # Get the SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        $commentRow  = $SQL->SELECT('*')->FROM('comments')->WHERE('id', '=', (int)$queries['comment_id'])->FIND();

        # If it doesn't exist return false
        if (!$commentRow) return false;

        $ip_address   = $commentRow['ip_address'];
        $name         = $commentRow['name'];
        $email        = $commentRow['email'];

        # Get all the user's comments
        $userComments = $SQL->SELECT('*')->FROM('comments')->WHERE('ip_address', '=', $ip_address)->OR_WHERE('email', '=', $email)->OR_WHERE('name', '=', $name)->FIND_ALL();

        $response     = [
            'reputation'   => 0,
            'posted_count' => 0,
            'spam_count'   => 0,
            'first_date'   => 0,
            'blacklisted'  => false,
            'whitelisted'  => false,
            'ip_address'   => $ip_address,
            'name'         => $name,
            'email'        => $email,
            'avatar'       => \Kanso\Kanso::getInstance()->Query->get_avatar($email, 150, true),
            'status'       => $commentRow['status'],
            'content'      => $commentRow['content'],
            'html_content' => $commentRow['html_content'],
        ];

        $blacklistedIps = \Kanso\Comments\Spam\SpamProtector::loadDictionary('blacklist_ip');
        $whiteListedIps = \Kanso\Comments\Spam\SpamProtector::loadDictionary('whitelist_ip');

        foreach ($userComments as $comment) {
           $response['reputation']   += $comment['rating'];
           $response['posted_count'] += 1;
           if ($comment['status'] === 'spam') $response['spam_count'] += 1;
           if ($comment['date'] < $response['first_date'] || $response['first_date'] === 0) $response['first_date'] = $comment['date'];
        }
        $response['reputation'] = $response['reputation']/ count($userComments);

        if (in_array($ip_address, $blacklistedIps)) $response['blacklisted'] = true;
        if (in_array($ip_address, $whiteListedIps)) $response['whitelisted'] = true;


        return $response;
    }

    /**
     * Update administrator settings
     *
     * This function updates the user's administrator settings.
     * i.e username, email and password.
     *
     * @param  $username       string
     * @param  $email          string
     * @param  $password       string
     * @return string|boolean
     */
    public function updateAccountDetails($username, $email, $password, $emailNotifications = true) 
    {
        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');

        # Validate that the username/ email doesn't exist already
        # only if the user has changed either value
        if ($email !== $sessionRow['email']) {
            $emailExists = $Query->SELECT('*')->FROM('users')->WHERE('email', '=', $email)->FIND();
            if ($emailExists) return 'email_exists';
        }
        if ($username !== $sessionRow['username']) {
            $usernameExists = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->FIND();
            if ($usernameExists) return 'username_exists';
        }

        # Grab the user's row from the database
        $userRow = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $sessionRow['username'])->AND_WHERE('email', '=', $sessionRow['email'])->AND_WHERE('status', '=', 'confirmed')->FIND();
        if (!$userRow || empty($userRow)) return false;

        # Sanitize email notifications
        if ($emailNotifications === 'true' || $emailNotifications === 1 || $emailNotifications === true) {
            $emailNotifications = true;
        }
        else {
            $emailNotifications = false;
        }

        # Update the username and email
        $row = [
            'username' => $username,
            'email'    => $email,
            'email_notifications' => $emailNotifications,
        ];

        # If they changed their password lets update it
        if ($password !== '' && !empty($password)) $row['hashed_pass'] = utf8_encode(\Kanso\Security\Encrypt::hash($password));

        # Save to the databse and refresh the client's session
        $update = $Query->UPDATE('users')->SET($row)->WHERE('id', '=', $userRow['id'])->QUERY();

        # If updated
        if ($update) {

            # Relog the client in
            \Kanso\Kanso::getInstance()->Session->refresh();

            return "valid";
        }

        return false;
    }

    /**
     * Update Author details
     *
     * @param  $name        string
     * @param  $slug        string
     * @param  $facebook    string
     * @param  $twitter     string
     * @param  $google      string
     * @return string|boolean
     */
    public function updateAuthorDetails($name, $slug, $bio, $facebook, $twitter, $google) 
    {

        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');

        # Grab the Row and update settings
        $userRow   = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $sessionRow['username'])->FIND();
        if (!$userRow) return false;

        # Change authors details
        $oldSlug  = $userRow['slug'];
        $userRow['name']        = $name;
        $userRow['slug']        = $slug;
        $userRow['facebook']    = $facebook;
        $userRow['twitter']     = $twitter;
        $userRow['gplus']       = $google;
        $userRow['description'] = $bio;

        # Save to the databse and refresh the client's session
        $update = $Query->UPDATE('users')->SET($userRow)->WHERE('id', '=', $userRow['id'])->QUERY();
        
        # Id updated
        if ($update) {

            # Relog the client in
            \Kanso\Kanso::getInstance()->Session->refresh();

            # Remove the old slug
            $this->removeAuthorSlug($oldSlug);

            # Add the new one
            $this->addAuthorSlug($slug);

            return 'valid';
        }

        return false;
    }

    /**
     * Add a slug to Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be added
     */
    private function addAuthorSlug($slug)
    {
        # Get the slugs
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        # Add the slug
        $slugs[] = $slug;

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_AUTHOR_SLUGS', array_unique(array_values($slugs)));
    }

    /**
     * Remove a slug from Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be removed
     */
    private function removeAuthorSlug($slug)
    {
        # Get the config
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        # Remove the slug
        foreach ($slugs as $i => $configSlug) {
            if ($configSlug === $slug) unset($slugs[$i]);
        }

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_AUTHOR_SLUGS', array_values($slugs));
    }

}