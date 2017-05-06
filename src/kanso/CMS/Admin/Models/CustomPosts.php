<?php

namespace Kanso\CMS\Admin\Models;

/**
 * GET/POST Model for custom posts pages
 *
 * This model is responsible for validating and parsing all
 * GET and POST requests made to the admin panel custom posts page.
 *
 */
class CustomPosts
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


    # What template should we load
    public function template()
    {
        return \Kanso\Kanso::getInstance()->Environment['KANSO_ADMIN_DIR'].DIRECTORY_SEPARATOR.'Views'.DIRECTORY_SEPARATOR.'dash-custom-posts.php';
    }

    /**
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * This method parses any URL queries to filter the article listings
     * e.g /admin/articles?status=published&page=3
     * 
     * @return array
     */
    public function onGet()
    {
        # Prep the response
        $response = [
            'articles'   => $this->loadArticles(),
            'max_page'   => 0,
            'queries'    => $this->getQueries(),
            'categories' => $this->getCategories(),
            'authors'    => $this->getAuthors(),
            'empty_queries' => $this->emptyQueries(),
        ];

        # If the articles are empty,
        # There's no need to check for max pages
        if (!empty($response['articles'])) {
            $response['max_page'] = $this->loadArticles(true);
        }

        return $response;
    }

    /**
     * Parse and validate the $_POST request variables from any submitted forms
     * 
     * @return array
     */
    public function onPost()
    {
        # Get the POST variables
        $_post = \Kanso\Kanso::getInstance()->Request->fetch();

        # Validation
        if (!isset($_post['bulk_action']) || empty($_post['bulk_action'])) return false;
        if (!in_array($_post['bulk_action'], ['published', 'draft', 'delete'])) return false;
        if (!isset($_post['posts']) || !is_array($_post['posts']) || empty($_post['posts'])) return false;
        
        # Get post ids
        $postIds = array_filter(array_map('intval', $_post['posts']));

        # Dispatch
        $response = false;

        if ($_post['bulk_action'] === 'delete') {
            $this->delete($postIds);
            return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your articles were successfully deleted!'];
        }
        else {
            $this->changeStatus($_post['bulk_action'], $postIds);
            return ['class' => 'success', 'icon' => 'check', 'msg' => 'Your articles were successfully updated!'];
        }

        return false;
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
            $queries['status'] === false && 
            $queries['author'] === false && 
            $queries['tag'] === false && 
            $queries['category'] === false
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
        if (!isset($queries['author']))   $queries['author']   = false;
        if (!isset($queries['tag']))      $queries['tag']      = false;
        if (!isset($queries['category'])) $queries['category'] = false;

        return $queries;
    }

    /**
     * Get all the authors from the DB
     * 
     * @return array
     */
    private function getAuthors()
    {
        return \Kanso\Kanso::getInstance()->Query->all_the_authors();
    }

    /**
     * Get all the categories from the DB
     * 
     * @return array
     */
    private function getCategories()
    {
        return \Kanso\Kanso::getInstance()->Query->all_the_categories();
    }

    /**
     * Change articles status
     *
     * @param  string    $status      The article status to change
     * @param  array     $postIds     Array of post ids
     *
     */
    private function changeStatus($status, $postIds) 
    {
        foreach ($postIds as $id) {
            \Kanso\Kanso::getInstance()->Bookkeeper->changeStatus($id, $status);
        }
    }

    /**
     * Change articles status
     *
     * @param  array     $postIds     Array of post ids
     *
     */
    private function delete($postIds) 
    {
        foreach ($postIds as $id) {
            \Kanso\Kanso::getInstance()->Bookkeeper->delete($id);
        }
    }

    /**
     * Load articles to display as list
     *
     * @param   boolean    $checkMaxPages    Don't return the articles just check how many pages there are
     * @return  array
     *
     */
    private function loadArticles($checkMaxPages = false)
    {
        # Get queries
        $queries = \Kanso\Kanso::getInstance()->Request->queries();

        # Get the post type based on the slug
        $urlParts = array_map('trim', explode('/', \Kanso\Kanso::getInstance()->Environment['REQUEST_URI']));
        $postType = '';
        foreach ($urlParts as $i => $urlPart) {
            if ($urlPart === 'admin') $postType = $urlParts[$i + 1];
        }

        # Set defaults
        if (!isset($queries['search']))   $queries['search']   = false;
        if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'newest';
        if (!isset($queries['status']))   $queries['status']   = false;
        if (!isset($queries['author']))   $queries['author']   = false;
        if (!isset($queries['tag']))      $queries['tag']      = false;
        if (!isset($queries['category'])) $queries['category'] = false;

        # Default operation values
        $page         = ((int)$queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'posts.created';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $status       = $queries['status'];
        $search       = $queries['search'];
        $author       = $queries['author'];
        $tag          = $queries['tag'];
        $category     = $queries['category'];

        # Filter and sanitize the sort order
        if ($queries['sort'] === 'newest' || $queries['sort'] === 'published') $sort = 'DESC';
        if ($queries['sort'] === 'oldest' || $queries['sort'] === 'drafts') $sort = 'ASC';

        if ($queries['sort'] === 'category')  $sortKey   = 'categories.name';
        if ($queries['sort'] === 'tags')      $sortKey   = 'tags.name';
        if ($queries['sort'] === 'drafts')    $sortKey   = 'posts.status';
        if ($queries['sort'] === 'published') $sortKey   = 'posts.status';
        if ($queries['sort'] === 'type')      $sortKey   = 'posts.type';
        if ($queries['sort'] === 'title')     $sortKey   = 'posts.title';

        # Get the Kanso Query builder
        $Query = \Kanso\Kanso::getInstance()->Query;

        # Get the Kanso SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        # Select the posts
        $SQL->SELECT('posts.*')->FROM('posts')->WHERE('posts.type', '=', $postType);

        # Set the limit - Only if we're returning the actual articles
        if (!$checkMaxPages) $SQL->LIMIT($offset, $limit);
        
        # Set the order
        $SQL->ORDER_BY($sortKey, $sort);

        # Apply basic joins for queries
        $SQL->LEFT_JOIN_ON('users', 'users.id = posts.author_id');
        $SQL->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id');
        $SQL->LEFT_JOIN_ON('categories', 'posts.category_id = categories.id');
        $SQL->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');
        $SQL->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');
        $SQL->GROUP_BY('posts.id');

        # Filter status/published
        if ($status === 'published') {
            $SQL->AND_WHERE('posts.status', '=', 'published');
        }
        else if ($status === 'drafts') {
            $SQL->AND_WHERE('posts.status', '=', 'draft');
        }

        # Search the title
        if ($search) {
            $SQL->AND_WHERE('posts.title', 'like', '%'.$queries['search'].'%');
        }

        # Filter by author
        if ($author) {
            $SQL->AND_WHERE('posts.author_id', '=', intval($author));
        }

        # Filter by tag
        if ($tag) {
            $SQL->AND_WHERE('tags.id', '=', intval($tag));
        }

        # Filter by category
        if ($category) {
            $SQL->AND_WHERE('category_id', '=', intval($category));
        }

        # Find the articles
        $articles = $SQL->FIND_ALL();

        # Add full joins as keys
        foreach ($articles as $i => $row) {
            $articles[$i]['tags']     = $SQL->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('tags_to_posts.post_id', '=', (int)$row['id'])->FIND_ALL();
            $articles[$i]['category'] = $SQL->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$row['category_id'])->FIND();
            $articles[$i]['author']   = $SQL->SELECT('*')->FROM('users')->WHERE('id', '=', (int)$row['author_id'])->FIND();
            $articles[$i]['excerpt']  = urldecode($row['excerpt']);
            $articles[$i]['comment_count']  = count($SQL->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$row['id'])->FIND_ALL());
        }

        # Loop and filter the articles
        foreach ($articles as $i => $article) {

            if ($article['status'] === 'draft') {
                $articles[$i]['permalink'] = rtrim($Query->the_permalink($article['id']), '/').'?draft';
            }
            else {
                $articles[$i]['permalink'] = $Query->the_permalink($article['id']);
            }

            $articles[$i]['edit_permalink'] = '/admin/write/'.$Query->the_slug($article['id']);

            $articles[$i]['category']['permalink'] = $Query->the_category_url($article['category_id']);


            foreach ($article['tags'] as $t => $tag) {
                $articles[$i]['tags'][$t]['permalink'] = $Query->the_tag_url($tag['id']);
            }
            
            $articles[$i]['author']['permalink'] = $Query->the_author_url($article['author_id']);

        }

        # Are we checking the pages ?
        if ($checkMaxPages) return ceil(count($articles) / $perPage);

        return $articles;
    }

}