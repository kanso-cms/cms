<?php

namespace Kanso\View;

/**
 * Query
 *
 * This class acts very similarly to WordPress's Query object. 
 * It can be initialized directly from a theme template to get
 * get a reference to Kanso's core 'front-end' functionality.
 *
 * It provides the ability to query the database on a pre-defined
 * set of tables and queries from a string, provding loop functionality.
 *
 * All template files within Kanso have a global reference to a 
 * Query object and it's methods
 *
 */
class Query {

    /**
     * @var    string    The page request type
     */
    public $requestType;

    /**
     * @var    int    The user defined query string
     */
    public $queryStr;

    /**
     * @var    int    Current page index of paginated array of posts
     */
    public $pageIndex;

    /**
     * @var    int    Current post index of paginated array of posts
     */
    public $postIndex = 0;

    /**
     * @var    int    Current post count
     */
    public $postCount = 0;

    /**
     * @var    array    paginated array of posts
     */
    public $posts = [];

    protected $searchQuery;

    /**
     * Constructor
     *
     * @param  string $queryStr       The string-query to use on the database
     * @param  string $requestType    Associative array of data made available to the view (optional)
     * @param  int    $pageIndex      The current page index e.g https://example.com/page3/
     */
    public function __construct($queryStr = '', $requestType = 'custom', $pageIndex = 0)
    {
        # Get the Kanso object instance
        $Kanso = \Kanso\Kanso::getInstance();

        # Set the index
        $this->postIndex    = 0;
        $this->pageIndex    = $Kanso->Request()->fetch('page');
        $this->pageIndex    = $this->pageIndex === 1 || $this->pageIndex === 0 ? 0 : $this->pageIndex-1;
        $this->queryStr     = trim($queryStr);
        $this->requestType  = $requestType;

        # Filter the posts directly from the constructor if
        # this is a custom Query request
        if ($requestType === 'custom' || empty($requestType) || trim($queryStr) === '') {
            $parser      = new QueryParser('');
            $this->posts = [$parser->parseQuery()];
        }

    }

    /**
     * Filter posts based on a request type
     *
     * @param  string $requestType    The requested page type (optional)
     */
    public function filterPosts($requestType = 'custom')
    {
        # Load the query parser
        $parser = new QueryParser($this->queryStr);

        # Set the request type
        $this->requestType = $requestType;

        # Save the requested URL
        $uri = rtrim(\Kanso\Kanso::getInstance()->Environment()['REQUEST_URI'], '/');

        # If the request type is custom load all the posts
        if ($requestType === 'custom' || empty($requestType)) {
            $this->posts = [$parser->parseQuery('')];
            return;
        }
        
        # Filter and paginate the posts based on the request type
        if ($requestType === 'home') {
            $this->queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC';
            $this->posts     = $parser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
            if ($this->posts) $this->posts = \Kanso\Utility\Arr::paginate($this->posts, $this->pageIndex, \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE']);
        }
        else if ($requestType === 'archive') {
            $this->queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC';
            $this->postCount = count($this->posts);
            $this->posts     = $parser->parseQuery($this->queryStr);
        }
        else if ($requestType === 'tag') {
            $this->queryStr = 'post_status = published : post_type = post : orderBy = post_created, DESC : tag_slug = '.explode("/", $uri)[1];
            $this->posts    = $parser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
            if ($this->posts) $this->posts = \Kanso\Utility\Arr::paginate($this->posts, $this->pageIndex, \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE']);
        }
        else if ($requestType === 'category') {
            $this->queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC : category_slug = '.explode("/", $uri)[1];
            $this->posts     = $parser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
            if ($this->posts) $this->posts = \Kanso\Utility\Arr::paginate($this->posts, $this->pageIndex, \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE']);
        } 
        else if ($requestType === 'author') {
            $this->queryStr = ' post_status = published : post_type = post : orderBy = post_created, DESC: author_slug = '.$slug;
            $this->posts    = $parser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
            if ($this->posts) $this->posts =  \Kanso\Utility\Arr::paginate($this->posts, $this->pageIndex, \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE']);
        }
        else if ($requestType === 'single') {
            if (strpos($uri,'?draft') !== false) {
                $uri = str_replace('?draft', '', $uri);
                $this->queryStr = 'post_status = draft : post_type = post : orderBy = post_created, DESC: post_slug = '.$uri.'/';
                $this->posts    = $parser->parseQuery($this->queryStr);
                $this->postCount = count($this->posts);
                $this->posts[0]  = $this->posts;
            }
            else {
                $uri = \Kanso\Utility\Str::GetBeforeLastWord($uri, '/feed');
                $uri = ltrim($uri, '/');
                $this->queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC : post_slug = '.$uri.'/';
                $this->posts     = $parser->parseQuery($this->queryStr);
                $this->postCount = count($this->posts);
                $this->posts[0]  = $this->posts;
            }
        } 
        else if ($requestType === 'static_page') {
            if (strpos($uri,'?draft') !== false) {
                $uri = str_replace('?draft', '', $uri);
                $this->queryStr = 'post_status = draft : post_type = page : orderBy = post_created, DESC : post_slug = '.$uri.'/';
                $this->posts    = $parser->parseQuery($this->queryStr);
                $this->postCount = count($this->posts);
                $this->posts[0]  = $this->posts;
            }
            else {
                $this->queryStr  = 'post_status = published : post_type = page : orderBy = post_created, DESC : post_slug = '.$uri.'/';
                $this->posts     = $parser->parseQuery($this->queryStr);
                $this->postCount = count($this->posts);
                $this->posts[0]  = $this->posts;
            }
        }
        else if ($requestType === 'search') {
            
            # Get the query
            $query = \Kanso\Kanso::getInstance()->Request()->fetch('query');
            
            # Validate the query exts
            if (!$query || empty(trim($query))) return;

            # Get the actual search query | sanitize
            $query = htmlspecialchars(trim(strtolower(urldecode(\Kanso\Utility\Str::getAfterLastChar($uri, '=')))));
            $query = \Kanso\Utility\Str::getBeforeFirstChar($query, '/');

            # No need to query empty strings
            if (empty($query)) return;

            # Filter the posts
            $this->queryStr  = "post_status = published : post_type = post : orderBy = post_created, DESC : post_title LIKE $query || post_excerpt LIKE $query";
            $this->posts     = $parser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
            if ($this->posts) $this->posts =  \Kanso\Utility\Arr::paginate($this->posts, $this->pageIndex, \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE']);
            $this->searchQuery = $query;
        }
    }

    /**
     * Tag exists
     *
     * @param   string    $tag_name
     * @return  bool  
     */
    public function tag_exists($tag_name)
    {
        $index    = is_numeric($tag_name) ? 'id' : 'name';
        $tag_name = is_numeric($tag_name) ? (int)$tag_name : $tag_name;
        return !empty(\Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('tags')->WHERE($index, '=', $tag_name)->FIND());
    }

    /**
     * Author exists
     *
     * @param   string    $author_name 
     * @return  bool  
     */
    public function author_exists($author_name)
    {
        $index       = is_numeric($author_name) ? 'id' : 'name';
        $author_name = is_numeric($author_name) ? (int)$author_name : $author_name;
        return !empty(\Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('authors')->WHERE($index, '=', $author_name)->FIND());
    }

    /**
     * Category Exists
     *
     * @param   string    $category_name 
     * @return  bool  
     */
    public function category_exists($category_name)
    {
        $index         = is_numeric($category_name) ? 'id' : 'name';
        $category_name = is_numeric($category_name) ? (int)$category_name : $category_name;
        return !empty(\Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('categories')->WHERE($index, '=', $category_name)->FIND());
    }

    /**
     * The post
     *
     * Increment the internal pointer by 1 and return the current post 
     * or just return a single post from the database by id
     * @param   int    $post_id (optional) 
     * @return  array|false 
     */
    public function the_post($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id);
            $post = $this->posts[$this->pageIndex][$this->postIndex];
            $this->_next();
            return $post;
        }
        return false;
    }

    /**
     * The title
     *
     * @param   int    $post_id (optional) 
     * @return  string|false
     */
    public function the_title($post_id = null)
    {

        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['title'];
            return $this->posts[$this->pageIndex][$this->postIndex]['title'];
        }
        return false;
    }

    /**
     * The permalink
     *
     * @param   int    $post_id (optional) 
     * @return  string|false
     */
    public function the_permalink($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/'.trim($this->getPostByID($post_id)['slug'], '/').'/';
            return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/'.trim($this->posts[$this->pageIndex][$this->postIndex]['slug'], '/').'/';
        }
        return false;
    }

    /**
     * The slug
     *
     * @param   int    $post_id (optional) 
     * @return  string|false
     */
    public function the_slug($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return trim($this->getPostByID($post_id)['slug'], '/').'/';
            return trim($this->posts[$this->pageIndex][$this->postIndex]['slug'], '/').'/';
        }
        return false;
    }

    /**
     * The excerpt
     *
     * @param   int    $post_id (optional) 
     * @return  string|false
     */
    public function the_excerpt($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            $excerpt = $post_id ? $this->getPostByID($post_id)['excerpt'] : $this->posts[$this->pageIndex][$this->postIndex]['excerpt'];
            return htmlspecialchars_decode($excerpt);
        }
        return false;
    }

    /**
     * The category
     *
     * @param   int    $post_id (optional) 
     * @return  string|false
     */
    public function the_category($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['category']['name'];
            return $this->posts[$this->pageIndex][$this->postIndex]['category']['name'];
        }
        return false;
    }

    /**
     * The category url
     *
     * @param   int    $category_id (optional) 
     * @return  string|false
     */
    public function the_category_url($category_id = null)
    {
        if (!$category_id) {
            if ($this->have_posts()) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/category/'.$this->posts[$this->pageIndex][$this->postIndex]['category']['slug'].'/';
        }
        else {
            $category = $this->getCategoryById($category_id);
            if ($category) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/category/'.$category['slug'].'/';
        }
        return false;
    }

    /**
     * The category slug
     *
     * @param   int    $category_id (optional) 
     * @return  string|false
     */
    public function the_category_slug($category_id = null)
    {
        if (!$category_id) {
            if ($this->have_posts()) return $this->posts[$this->pageIndex][$this->postIndex]['category']['slug'];
        }
        else {
            $category = $this->getCategoryById($category_id);
            if ($category) return $category['slug'];
        }
        return false;
    }

    /**
     * The category id
     *
     * @param   string   $category_name (optional) 
     * @return  int|false
     */
    public function the_category_id($category_name = null)
    {
        if (!$category_name) {
            if ($this->have_posts()) return $this->posts[$this->pageIndex][$this->postIndex]['category']['id'];
        }
        else {
            $category = $this->getCategoryByName($category_id);
            if ($category) return $category['id'];
        }
        return false;
    }

    /**
     * The tags
     *
     * @param   int   $post_id (optional) 
     * @return  array
     */
    public function the_tags($post_id = null) 
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['tags'];
            return $this->posts[$this->pageIndex][$this->postIndex]['tags'];
        }
        return [];
    }

    /**
     * The tags
     *
     * @param   int   $post_id (optional) 
     * @return  string
     */
    public function the_tags_list($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) \Kanso\Utility\Arr::implodeByKey('name', $this->getPostByID($post_id)['tags'], ', ');
            return \Kanso\Utility\Arr::implodeByKey('name', $this->posts[$this->pageIndex][$this->postIndex]['tags'], ', ');
        }
        return '';
    }

    /**
     * The Tags Slug
     *
     * @param   int   $tag_id 
     * @return  string|false
     */
    public function the_tag_slug($tag_id) 
    {
        $tag = $this->getTagById($tag_id);
        if ($tag) return $tag['slug'];
        return false;
    }

    /**
     * The Tags URL
     *
     * @param   int   $tag_id 
     * @return  string|false
     */
    public function the_tag_url($tag_id) 
    {
        $tag = $this->getTagById($tag_id);
        if ($tag) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/tag/'.$tag['slug'];
        return false;
    }

    /**
     * The content
     *
     * @param   int   $post_id (optional) 
     * @return  string|false
     */
    public function the_content($post_id = null) 
    {
        if ($this->have_posts($post_id)) {
            $content = $post_id ? $this->getPostContent($post_id) : $this->getPostContent($this->posts[$this->pageIndex][$this->postIndex]['id']);
            if (empty($content)) return '';
            if (is_array($content) && isset($content['content'])) $content = $content['content'];
            $Parser  = new \Kanso\Parsedown\ParsedownExtra();
            return $Parser->text(htmlspecialchars_decode($content));
        }
        return false;
    }
    
    /**
     * The post thumbnail
     *
     * @param   string   $size    (optional) "small/medium/large"
     * @param   int      $post_id (optional)
     * @return  string|false
     */
    public function the_post_thumbnail($size = 'large', $post_id = null) 
    {

        if ($this->have_posts($post_id)) {
            if ($this->has_post_thumbnail($post_id)) {
                if ($post_id) return \Kanso\Kanso::getInstance()->Environment()['KANSO_IMGS_URL'].str_replace('_large', '_'.$size, $this->getPostByID($post_id)['thumbnail']);
                return \Kanso\Kanso::getInstance()->Environment()['KANSO_IMGS_URL'].str_replace('_large', '_'.$size, $this->posts[$this->pageIndex][$this->postIndex]['thumbnail']);
            }
        }
        return false;
    }

    /**
     * The author
     *
     * @param   int      $post_id (optional)
     * @return  string|false
     */
    public function the_author($post_id = null) 
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getAuthorById($this->getPostByID($post_id)['author']['name']);
            return $this->posts[$this->pageIndex][$this->postIndex]['author']['name'];
        }
        return false;
    }

    /**
     * The author url 
     *
     * @param   int      $author_id (optional)
     * @return  string|false
     */
    public function the_author_url($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/authors/'.$author['slug'];
        }
        else if ($this->have_posts()) {
            return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/authors/'.$this->posts[$this->pageIndex][$this->postIndex]['author']['slug'];
        }
        return false;
    }

    /**
     * The author thumbnail 
     *
     * @param   string   $size      (optional) "small/medium/large"
     * @param   int      $author_id (optional)
     * @return  string|false
     */
    public function the_author_thumbnail($size = 'small', $author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return \Kanso\Kanso::getInstance()->Environment()['KANSO_IMGS_URL'].str_replace('_large', '_'.$size, $author['thumbnail']);
        }
        if ($this->have_posts()) {
            if (!empty($this->posts[$this->pageIndex][$this->postIndex]['author']['thumbnail'])) {
                return \Kanso\Kanso::getInstance()->Environment()['KANSO_IMGS_URL'].str_replace('_large', '_'.$size, $this->posts[$this->pageIndex][$this->postIndex]['author']['thumbnail']);
            }
        }
        return false;
    }

    /**
     * The author bio 
     *
     * @param   int      $author_id (optional)
     * @return  string|false
     */
    public function the_author_bio($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return $author['description'];
        }
        if ($this->have_posts()) {
            return $this->posts[$this->pageIndex][$this->postIndex]['author']['description'];
        }
        return false;
    }

    /**
     * The author twitter 
     *
     * @param   int      $author_id (optional)
     * @return  string|false
     */
    public function the_author_twitter($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return $author['twitter'];
        }
        if ($this->have_posts()) {
            $author = $this->getAuthorById($this->posts[$this->pageIndex][$this->postIndex]['author_id']);
            if ($author) return $author['twitter'];
        }
        return false;
    }

    /**
     * The author google 
     *
     * @param   int      $author_id (optional)
     * @return  string|false
     */
    public function the_author_google($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return $author['gplus'];
        }
        if ($this->have_posts()) {
            $author = $this->getAuthorById($this->posts[$this->pageIndex][$this->postIndex]['author_id']);
            if ($author) return $author['gplus'];
        }
        return false;
    }

    /**
     * The author facebook 
     *
     * @param   int      $author_id (optional)
     * @return  string|false
     */
    public function the_author_facebook($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return $author['facebook'];
        }
        if ($this->have_posts()) {
            $author = $this->getAuthorById($this->posts[$this->pageIndex][$this->postIndex]['author_id']);
            if ($author) return $author['facebook'];
        }
        return false;
    }

    /**
     * The post ID 
     *
     * @return  int|false
     */
    public function the_post_id() 
    {
        if ($this->have_posts()) return $this->posts[$this->pageIndex][$this->postIndex]['id'];
        return false;
    }

    /**
     * The post status 
     *
     * @param   int      $post_id (optional)
     * @return  string|false
     */
    public function the_post_status($post_id = null) 
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['status'];
            return $this->posts[$this->pageIndex][$this->postIndex]['status'];
        }
        return false;
    }

    /**
     * The post type 
     *
     * @param   int      $post_id (optional)
     * @return  string|false
     */
    public function the_post_type($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['type'];
            return $this->posts[$this->pageIndex][$this->postIndex]['type'];
        }
        return false;
    }

    /**
     * The time 
     *
     * @param   string   $format  (optional)
     * @param   int      $post_id (optional)
     * @return  string|int|false
     */
    public function the_time($format = 'U', $post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return date($format, $this->getPostByID($post_id)['created']);
            return date($format, $this->posts[$this->pageIndex][$this->postIndex]['created']);
        }
        return false;
    }

    /**
     * The modified time 
     *
     * @param   string   $format  (optional)
     * @param   int      $post_id (optional)
     * @return  string|int|false
     */
    public function the_modified_time($format = 'U', $post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return date($format, $this->getPostByID($post_id)['modified']);
            return date($format, $this->posts[$this->pageIndex][$this->postIndex]['modified']);
        }
        return false;
    }

    /**
     * The author posts 
     *
     * @param   int      $author_id
     * @return  array
     */
    public function the_author_posts($author_id)
    {
        if ($this->author_exists($author_id)) {
            return \Kanso\Kanso::getInstance()->CRUD()->getArticlesByIndex('author_id', (int)$author_id);
        }
        return false;
    }

    /**
     * The category posts 
     *
     * @param   int      $category_id
     * @return  array
     */
    public function the_category_posts($category_id)
    {
        if ($this->category_exists($category_id)) {
            return \Kanso\Kanso::getInstance()->CRUD()->getArticlesByIndex('category_id', (int)$category_id);
        }
        return false;
    }

    /**
     * The tag posts 
     *
     * @param   int      $tag_id
     * @return  array
     */
    public function the_tag_posts($tag_id)
    {
        if ($this->tag_exists($tag_id)) {
            return \Kanso\Kanso::getInstance()->CRUD()->getArticlesByIndex('tags.id', (int)$tag_id);
        }
    }

    /**
     * The page type 
     *
     * @return  string
     */
    public function the_page_type()
    {
        return $this->requestType;
    }

    /**
     * Is single
     *
     * @return  bool
     */
    public function is_single()
    {
        return $this->requestType === 'single';
    }

    /**
     * Is home
     *
     * @return  bool
     */
    public function is_home()
    {
        return $this->requestType === 'home';
    }

    /**
     * Is front page
     *
     * @return  bool
     */
    function is_front_page()
    {
       return $this->pageIndex === 0;
    }

    /**
     * Is page
     *
     * @return  bool
     */
    public function is_page()
    {
        return $this->requestType === 'page';
    }

    /**
     * Is archive
     *
     * @return  bool
     */
    public function is_archive()
    {
        return $this->requestType === 'archive';
    }

    /**
     * Is search
     *
     * @return  bool
     */
    public function is_search()
    {
        return $this->requestType === 'search';
    }

    /**
     * Is tag
     *
     * @return  bool
     */
    public function is_tag()
    {
        return $this->requestType === 'tag';
    }

    /**
     * Is category
     *
     * @return  bool
     */
    public function is_category()
    {
        return $this->requestType === 'category';
    }

    /**
     * Is author
     *
     * @return  bool
     */
    public function is_author()
    {
        return $this->requestType === 'author';
    }

    /**
     * Is admin
     *
     * @return  bool
     */
    public function is_admin()
    {
        return \Kanso\Kanso::getInstance()->is_admin;
    }

    /**
     * Has post Thumbnail
     *
     * @param   int   $post_id   (optional)
     * @return  bool
     */
    public function has_post_thumbnail($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return trim($this->getPostByID($post_id)['thumbnail']) !== "";
            return trim($this->posts[$this->pageIndex][$this->postIndex]['thumbnail']) !== "";
        }
        return false;

    }

    /**
     * Has author Thumbnail
     *
     * @param   int   $post_id   (optional)
     * @return  bool
     */
    public function has_author_thumbnail($author_id)
    {
        $author = $this->getAuthorById($author_id);
        if ($author) return $author['thumbnail'] !== '';
        return false;
    }

    /**
     * Has excerpt
     *
     * @param   int   $post_id   (optional)
     * @return  bool
     */
    public function has_excerpt($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['excerpt'] !== '';
            return $this->posts[$this->pageIndex][$this->postIndex]['excerpt'] !== '';
        }
        return false;
    }

    /**
     * Has tags
     *
     * @param   int   $post_id   (optional)
     * @return  bool
     */
    public function has_tags($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            $tags = $post_id ? $this->getPostByID($post_id)['tags'] : $this->posts[$this->pageIndex][$this->postIndex]['tags'];
            if ($post_id) return count($tags) > 1;
            return count($tags) > 1;
        }
        return false;
    }

    /**
     * Has category
     *
     * @param   int   $post_id   (optional)
     * @return  bool
     */
    public function has_category($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['category']['id'] !== 1;
            return $this->posts[$this->pageIndex][$this->postIndex]['category']['id'] !== 1;
        }
        return false;
    }

    /**
     * The page title
     *
     * @return  string
     */
    public function the_page_title()
    {
        $uri        = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['PATH_INFO'], '/'));
        $PageType   = $this->requestType;
        $titleBase  = \Kanso\Kanso::getInstance()->Config()['KANSO_SITE_TITLE'];
        $titlePage  = $this->pageIndex > 0 ? 'Page '.($this->pageIndex+1).' | ' : '';
        $titleTitle = '';

        if ($this->is_single()) {
            $titleTitle = $this->posts[$this->pageIndex][$this->postIndex]['title'].' | ';
        }
        else if ($this->is_tag()) {
            foreach ($this->posts[$this->pageIndex][$this->postIndex]['tags'] as $tag) {
                if ($tag['slug'] === $uri[1]) {
                    $titleTitle = $tag['name'].' | ';
                }
            }
        }
        else if ($this->is_category()) {
            $titleTitle = $this->posts[$this->pageIndex][$this->postIndex]['category']['name'].' | ';
        }
        else if ($this->is_author()) {
            $titleTitle = $this->posts[$this->pageIndex][$this->postIndex]['author']['name'].' | ';
        }
        else if ($this->is_search()) {
            $titleTitle = 'Search Results |';
        }
        else if ($this->is_archive()) {
            $titleTitle = 'Archive |';
        }

        return  $titleTitle.$titlePage.$titleBase;
    }

    /**
     * The next page
     *
     * @return  array|false
     */
    public function the_next_page()
    {
        if ($this->requestType === 'page') return false;

        if ($this->have_posts()) {
            if ($this->is_single()) {
                return $this->findNextPost($this->posts[$this->pageIndex][$this->postIndex]);
            }
            else if (isset($this->posts[$this->pageIndex+1])) {
                $nextPage   = $this->pageIndex+2;
                $uri        = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['PATH_INFO'], '/'));
                $PageType   = $this->requestType;
                $titleBase  = \Kanso\Kanso::getInstance()->Config()['KANSO_SITE_TITLE'];
                $titlePage  = $nextPage > 1 ? 'Page '.$nextPage.' | ' : '';
                $titleTitle = '';
                if ($this->is_home() ) {
                    $slug = 'page/'.$nextPage.'/';
                }
                else if ($this->is_tag() || $this->is_category() || $this->is_author() ) {
                    $titleTitle = $uri[1].' | ';
                    $slug = $uri[0].'/'.$uri[1].'/page/'.$nextPage.'/';
                }
                else if ($this->is_search()) {
                    $titleTitle = 'Search Results | ';
                    $slug       = $uri[0].'/'.$uri[1].'/page/'.$nextPage.'/';
                }
                return [
                    'title' => $titleTitle.$titlePage.$titleBase,
                    'slug'  => $slug,
                ];
            }
        }
        return false;
    }

    /**
     * The previous page
     *
     * @return  array|false
     */

    public function the_previous_page()
    {
        if ($this->requestType === 'page') return false;

        if ($this->have_posts()) {
            if ($this->is_single()) {
                return $this->findPrevPost($this->posts[$this->pageIndex][$this->postIndex]);
            }
            else if (isset($this->posts[$this->pageIndex-1])) {
                $prevPage   = $this->pageIndex;
                $uri        = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['PATH_INFO'], '/'));
                $PageType   = $this->requestType;
                $titleBase  = \Kanso\Kanso::getInstance()->Config()['KANSO_SITE_TITLE'];
                $titlePage  = $prevPage > 1 ? 'Page '.$prevPage.' | ' : '';
                $titleTitle = '';
                if ($this->is_home() ) {
                    $slug =  $prevPage > 1 ? 'page/'.$prevPage.'/' : '';
                }
                else if ($this->is_tag() || $this->is_category() || $this->is_author()) {
                    $titleTitle = $uri[1].' | ';
                    $slug       =  $prevPage > 1 ? $uri[0].'/'.$uri[1].'/page/'.$prevPage.'/' : $uri[0].'/'.$uri[1].'/';
                }
                else if ($this->is_search()) {
                    $titleTitle = 'Search Results | ';
                    $slug       = $prevPage > 1 ? $uri[0].'/'.$uri[1].'/page/'.$prevPage.'/' : $uri[0].'/'.$uri[1].'/';
                }
                return [
                    'title' => $titleTitle.$titlePage.$titleBase,
                    'slug'  => $slug,
                ];
            }
        }
        return false;
    }

    /**
     * The next page title
     *
     * @return  string|false
     */
    public function the_next_page_title()
    {
        $next_page = $this->the_next_page();
        if ($next_page && isset($next_page['title'])) return $next_page['title'];
        return false;
    }

    /**
     * The previous page title
     *
     * @return  string|false
     */
    public function the_previous_page_title()
    {
        $prev_page = $this->the_previous_page();
        if ($prev_page && isset($prev_page['title'])) return $prev_page['title'];
        return false;
    }

    /**
     * The next page url
     *
     * @return  string|false
     */
    public function the_next_page_url()
    {
        $next_page = $this->the_next_page();
        if ($next_page && isset($next_page['slug'])) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/'.$next_page['slug'];
        return false;
    }

    /**
     * The previous page url
     *
     * @return  string|false
     */
    public function the_previous_page_url()
    {
        $prev_page = $this->the_previous_page();
        if ($prev_page && isset($prev_page['slug'])) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/'.$prev_page['slug'];
        return false;
    }

    /**
     * The search query
     *
     * @return  string|false
     */
    public function search_query()
    {
        if ($this->is_search()) {
            return $this->searchQuery;
        }
        return false;
    }

    /**
     * The posts count
     *
     * @return  integer
     */
    public function the_posts_count()
    {
        return $this->postCount;
    }

    /**
     * How many posts are displayed per page
     *
     * @return  integer
     */
    public function posts_per_page()
    {
        return \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE'];
    }

    /**
     * Do we have posts? 
     *
     * @param   int  $post_id (optional)
     * @return  bool
     */
    public function have_posts($post_id = null)
    {
        if ($post_id) return !empty($this->getPostByID($post_id));
        return isset($this->posts[$this->pageIndex][$this->postIndex]);
    }

    /**
     * Next post
     */
    public function _next()
    {
        $this->postIndex++;
    }

    /**
     * Previous post
     */
    public function previous()
    {
        $this->postIndex--;
    }

    /**
     * Next page
     */
    public function next_page()
    {
        $this->pageIndex++;
        $this->postIndex = 0;
    }

    /**
     * Previous page
     */
    public function previus_page()
    {
        $this->pageIndex--;
        $this->postIndex = 0;
    }

    /**
     * All the tags 
     * @return array
     */
    public function all_the_tags()
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('tags')->FIND_ALL();
    }

    /**
     * All the categories 
     * @return array
     */
    public function all_the_categories()
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('categories')->FIND_ALL();
    }

    /**
     * All the authors 
     * @return array
     */
    public function all_the_authors()
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('authors')->FIND_ALL();
    }

    /**
     * The header
     * @return string
     */
    public function the_header()
    {
        ob_start();
            require_once \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'].DIRECTORY_SEPARATOR.'header.php';
        return ob_get_clean();
    }

    /**
     * The footer
     * @return string
     */
    public function the_footer()
    {
        ob_start();
            require_once \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'].DIRECTORY_SEPARATOR.'footer.php';
        return ob_get_clean();
    }

    /**
     * The sidebar
     *
     * @return string
     */
    public function the_sidebar()
    {
        ob_start();
            require_once \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'].DIRECTORY_SEPARATOR.'sidebar.php';
        return ob_get_clean();
    }

    /**
     * Include a template from current theme
     *
     * @param  string $template_name Name of template in current them
     * @return string
     */
    public function include_template($template_name, $data = null)
    {
        $template = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'].DIRECTORY_SEPARATOR.$template_name.'.php';
        if (file_exists($template)) {
            ob_start();
            if ($data && is_array($data)) extract($data);
            include $template;
            return ob_get_clean();
        }
        return '';
    }

    /**
     * Get the theme directory
     *
     * @return string
     */
    public function theme_directory() 
    {
        return \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'];
    }

    /**
     * Get the theme url
     *
     * @return string
     */
    public function theme_url() 
    {
        return str_replace(\Kanso\Kanso::getInstance()->Environment()['DOCUMENT_ROOT'], \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'], \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME']);
    }

    /**
     * Get the homepage url
     *
     * @return string
     */
    public function home_url() 
    {
        return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'];
    }

    /**
     * Get the website base name
     *
     * @return string
     */
    public function website_name() 
    {
        return \Kanso\Kanso::getInstance()->Environment()['KANSO_WEBSITE_NAME'];
    }

    /**
     * Get the website base title
     *
     * @return string
     */
    public function website_title() 
    {
        return \Kanso\Kanso::getInstance()->Config()['KANSO_SITE_TITLE'];
    }

    /**
     * Get the website description
     *
     * @return string
     */
    public function website_description() 
    {
        return \Kanso\Kanso::getInstance()->Config()['KANSO_SITE_DESCRIPTION'];
    }

    /**
     * Get all static pages
     *
     * @return array
     */
    public function static_pages() 
    {

        $articles = \Kanso\Kanso::getInstance()->CRUD()->getArticlesByIndex('type', '=', 'page');
        foreach ($articles as $i => $article) {
            if ($article['status'] !== 'published') unset($articles[$i]);
        }
        return $articles;
    }

    /**
     * Get the currently logged in Kanso user (if any)
     *
     * @return array
     */
    public function get_current_userinfo() 
    {

        if ($this->is_loggedin()) {
            \Kanso\Admin\Security\sessionManager::init();
            return \Kanso\Admin\Security\sessionManager::get();
        }
        return [];
    }

    /**
     * Validate that the current user is logged in to Kanso's admin panel
     * @return bool
     */
    public function is_loggedin() 
    {
        \Kanso\Admin\Security\sessionManager::init();
        return \Kanso\Admin\Security\sessionManager::isLoggedIn();
    }

    /**
     * Validate that an article has comments enabled or not
     * Or if comments are globally disabled
     * @return bool
     */
    public function comments_open($post_id = null) 
    {
        if (\Kanso\Kanso::getInstance()->Config()['KANSO_COMMENTS_OPEN'] === false) return false;
        if ($this->have_posts($post_id)) {
            if ($post_id) return $this->getPostByID($post_id)['comments_enabled'];
            return $this->posts[$this->pageIndex][$this->postIndex]['comments_enabled'];
        }
        return false;
    }

    /**
     * Validate that an article has comments or not
     * @return bool
     */
    public function has_comments($post_id = null) 
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return !empty($this->getPostByID($post_id)['comments']);
            return !empty($this->posts[$this->pageIndex][$this->postIndex]['comments']);
        }
        return false;
    }

    /**
     * Get a comment count on a given article
     * @return int
     */
    public function comments_number($post_id = null)
    {
        if ($this->have_posts($post_id)) {
            if ($post_id) return count($this->getPostByID($post_id)['comments']);
            return count($this->posts[$this->pageIndex][$this->postIndex]['comments']);
        }
        return 0;
    }

    /**
     * Get a single comment by id
     * @return array
     */
    public function get_comment($comment_id)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('comments')->WHERE('id', '=', $comment_id)->LIMIT(1)->FIND();
    }

    /**
     * Get an article's comments  
     * @return array
     */
    public function get_comments($post_id = null, $approvedOnly = true)
    {
        
        if ($this->have_posts($post_id)) {
            $post_id  = !$post_id ? $this->posts[$this->pageIndex][$this->postIndex]['id'] : $post_id;
            $CRUD     = \Kanso\Kanso::getInstance()->CRUD();
            $CRUD->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$post_id);
            if ($approvedOnly) $CRUD->AND_WHERE('status', '=', 'approved');
            return $CRUD->FIND_ALL();
        }
        return [];
    }

    /**
     * Get a comment count on a given article
     * @return int
     */
    public function display_comments($args = null, $post_id = null)
    {

        # Are there comments to loop
        $have_comments = $this->have_posts($post_id) && $this->comments_number($post_id) > 0;

        # HTML string
        $HTML = '';

        # If there no comments return empty string
        if (!$have_comments) return $HTML;

        # Save the article row locally
        $articleRow  = !$post_id ? $this->posts[$this->pageIndex][$this->postIndex] : $this->getPostByID($post_id);

        # Save the article permalink locally
        $permalink   = $this->the_permalink($post_id);

        # Default comment format
        $defaultFormat = '
            <div (:classes_wrap) data-comment-id="(:id)">
                
                <div (:classes_author_wrap)>
                    <div (:classes_avatar_wrap)>
                        <img alt="" src="(:avatar_src)" (:classes_avatar_img) width="(:avatar_size)" height="(:avatar_size)" />
                    </div>
                    <p (:classes_name)>(:comment_name)</p>
                    
                </div>

                <div (:classes_body)>
                    <div (:classes_content)>
                        (:comment_content)
                    </div>
                </div>

                <div (:classes_footer)>
                    <time (:classes_time) datetime="(:comment_time_GMT)">(:comment_time_format)</time> • 
                    <a (:classes_link) href="(:permalink)#(:id)">(:link_text)</a> • 
                    <a (:classes_reply) href="#">Reply</a>
                </div>

                <div (:classes_children_wrap)>
                    (:children)
                </div>

            </div>
        ';

        # Default options
        $options = [
            'format'             => null,
            'avatar_size'        => 160,
            'link_text'          => '#',
            'time_format'        => 'F, d, Y',
            'classes'            => [
                    'wrap'          => 'comment-comment-wrap',
                    'avatar_wrap'   => 'comment-avatar-wrap',
                    'avatar_img'    => 'comment-avatar-img',
                    'body'          => 'comment-comment-body',
                    'author_wrap'   => 'comment-author-wrap',
                    'name'          => 'comment-author-name',
                    'link'          => 'comment-comment-link',
                    'time'          => 'comment-comment-time',
                    'content'       => 'comment-comment-content',
                    'footer'        => 'comment-comment-footer',
                    'reply'         => 'comment-reply-link',
                    'children_wrap' => 'comment-comment-chidren',
                    'child_wrap'    => 'comment-child-comment',
                    'no_children'   => 'comment-no-children',
                ],
        ];

        # If options were set, overwrite the dafaults
        if ($args && is_array($args)) $options = array_merge($options, $args);

        # Set the default format if not provided
        if (!$options['format']) $options['format'] = $defaultFormat;
        
        # Get the comments as multi-dimensional array
        $comments = $articleRow['comments'];

        # If there was an error retrieving the comments return empty string
        if (empty($comments)) return $HTML;

        # Load from template if it exists
        $formTemplate = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'].DIRECTORY_SEPARATOR.'comments.php';
        if (file_exists($formTemplate)) {
            return $this->include_template('comments', ['comments' => $this->buildCommentTree($comments)] );
        }

        # Start looping comments
        $HTML = $this->commentToString($this->buildCommentTree($comments), $options, $permalink, false); 

        return $HTML;
    }

    /**
     * Get a comment count on a given article
     * @return int
     */
    public function comment_form($args = null, $post_id = null)
    {
        # Load from template if it exists
        $formTemplate = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'].DIRECTORY_SEPARATOR.'commentform.php';
        if (file_exists($formTemplate)) return $this->include_template('commentform');
      
        # HTML string
        $HTML = '';

        # Save the article row locally
        $articleRow  = !$post_id ? $this->posts[$this->pageIndex][$this->postIndex] : $this->getPostByID($post_id);

        # Save the article permalink locally
        $permalink   = $this->the_permalink($post_id);

        # Save the article id locally
        $postID = $articleRow['id'];

        $options = [

            'form_class' => 'comment-form',

            'reply_id' => '',

            'legend' => '<legend>Leave a comment:</legend>',

            'comment_field' => '<label for="content">Comment:</label>
                                <textarea type="text" name="content (required)" placeholder="Leave a comment..." autocomplete="off"></textarea>',

            'name_field' => '<label for="name">Name:</label>
                             <input type="text" name="name" placeholder="Name (required)" autocomplete="off" />',

            'email_field' => '<label for="email">Email:</label>
                              <input type="email" name="email" placeholder="Email (required)" autocomplete="off" />',

            'email_replies_field' => '<input type="checkbox" name="email-reply" /> Notify me of follow-up comments by email:<br>',

            'email_thread_field'  => '<input type="checkbox" name="email-thread" /> Notify me of all comments on this post by email:<br>',

            'post_id_field'  => '<input type="hidden" name="postID" style="display:none" value="(:postID)" />',

            'reply_id_field' => '<input type="hidden" name="replyID" style="display:none" value="(:replyID)" />',

            'submit_field'   => '<button type="submit" value="submit">Submit</button>',
        ];

        # If options were set, overwrite the dafaults
        if ($args && is_array($args)) $options = array_merge($options, $args);

        # Replace POSTID and REPLY ID
        $patterns     = ['/\(:postID\)/','/\(:replyID\)/'];
        $replacements = [$postID, $options['reply_id']];

        # No replies when comments are disabled
        if (!$this->comments_open($post_id)) {
            $options['reply_id_field'] = '';
        }

        # Default form format
        return preg_replace($patterns, $replacements,'
           <form class="'.$options['form_class'].'">
                <fieldset>
                    '.$options['legend'].'
                    '.$options['name_field'].'
                    '.$options['email_field'].'
                    '.$options['comment_field'].'
                    '.$options['email_replies_field'].'
                    '.$options['email_thread_field'].'
                    '.$options['post_id_field'].'
                    '.$options['reply_id_field'].'
                    '.$options['submit_field'].'
                </fieldset>
            </form>
        ');
        
    }

    /**
     * Retrieve the avatar 'img' tag from an email address or md5 hash. 
     * @param  string      $email_address    The email address or md5 of the current user (optional)
     * @param  int         $size             Image size in px
     * @param  bool        $srcOnly          Should we return only the img src (rather than the actual HTML tag)
     * @return string      user's avatar or default mystery on fallback
     */
    public function get_avatar($email_or_md5, $size = 160, $srcOnly = null) 
    {

        $isMd5   = $this->isValidMd5($email_or_md5);
        
        $isEmail = !filter_var($email_or_md5, FILTER_VALIDATE_EMAIL) === false;

        $domain = \Kanso\Kanso::getInstance()->Environment()['HTTP_PROTOCOL'] === 'https' ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';

        # If there is an error with the emaill or md5 default to fallback 
        # force a mystery man
        if (!$isMd5 && !$isEmail) {
            if ($srcOnly) return $domain.'/avatar/0?s='.$size.'&d=mm&f=y';
            return '<img src="'.$domain.'/avatar/0?s='.$size.'&d=mm&f=y"/>';
        }
        
        if ($isEmail) $md5 = md5( strtolower( trim( $email_or_md5 ) ) );
        if ($isMd5)   $md5 = $email_or_md5;
       
        if ($srcOnly) return $domain.'/avatar/'.$md5.'?s='.$size.'&d=mm&f=y';
        return '<img src="'.$domain.'/avatar/'.$md5.'?s='.$size.'&d=mm&f=y"/>';
    
    }

    /**
     * Build HTML Pagination links
     *
     * @param  array       $args    Associative array of options (optional)
     */
    public function pagination_links($args = null) 
    {

        # Default options
        $options = [
          'base'               => \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'],
          'format'             => '<li class="(:class)"><a href="(:link)">(:num)</a></li>',
          'current'            => 1,
          'total'              => 1,
          'context'            => 2,
          'show_all'           => false,
          'prev_next'          => true,
          'ellipsis'           => '<li>. . .</li>',
          'prev_text'          => '« Previous',
          'next_text'          => 'Next »',
        ];

        # Segment the reuest URI
        $uri = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['PATH_INFO'], '/'));

        # Declare the pagination string
        $pagination = '';

        # If no args were defined, Kanso will figure it out for us
        if (!$args || !isset($args['current']) || !isset($args['total'])) {
            # pages here are used as for an array so +1 
            $options['current'] = $this->pageIndex === 0 ? 1 : $this->pageIndex+1;
            $options['total']   = count($this->posts);
        }

        # If options were set, overwrite the dafaults
        if ($args) $options = array_merge($options, $args);

        #Special case if there is only 1 page
        if ($options['total'] == 1 || $options['total'] == 0 || $options['total'] < 1) return '';

        # Clean the base url
        $options['base'] = rtrim($options['base'], '/');

        # Update the base url depending on the page type
        if ($this->is_search()) {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.'search-results/?q='.$this->searchQuery;
        }
        else if ($this->is_archive()) {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.'archive';
        }
        else if ($this->is_tag()) {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[1];
        }
        else if ($this->is_category()) {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[1];
        }
        else if ($this->is_author()) {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[1];
        }

        # loop always at the current minus the context, minus 1
        $loopStart  = ($options['current'] - $options['context']);

        # if the loop starts before 2, reset it to 2
        if ($loopStart < 2) $loopStart = 2;

        # Loop end is the context * 2 + loop start + plus 1
        $loopEnd    = $loopStart + ($options['context'] * 2) + 1 ;

        # We should show all links if the loop ends after the total
        if ($loopEnd >= $options['total'] || $options['show_all'] === true) $loopEnd = $options['total'];

        # Declare variables we are going to use
        $frontEllipsis = $loopStart > 2 ? $options['ellipsis'] :  '';
        $backEllipsis  = $loopEnd === $options['total'] || $options['total'] - $options['context'] === $loopEnd ? '' : $options['ellipsis'] ;

        # Variables we will need
        $patterns     = ['/\(:class\)/','/\(:link\)/', '/\(:num\)/'];
        $replacements = [];

        # If show all is true we need reset
        if ($options['show_all'] === true) {
            $frontEllipsis = '';
            $backEllipsis  = '';
            $loopStart     = 2;
            $loopEnd       = $options['total'];
        }
        
        # If show previous
        if ($options['prev_next'] === true) {
            $class = $options['current'] === 1  ? 'disabled' : '';
            $link  = $options['current'] === 1  ? '#' : $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.($options['current']-1).DIRECTORY_SEPARATOR;
            $link  = $options['current'] === 2  ? $options['base'] : $link;
            $replacements = [$class, $link, $options['prev_text']];
            $pagination  .= preg_replace($patterns, $replacements, $options['format']);
            $replacements = [];
        }

        # Show the first page
        $class = $options['current'] === 1  ? 'active' : '';
        $link  = $options['current'] === 1  ? '#' : $options['base'];
        $replacements = [$class, $link, 1];
        $pagination  .= preg_replace($patterns, $replacements, $options['format']);
        $replacements = [];

        # Show the front ellipsis
        $pagination .= $frontEllipsis;

        # Loop over the pages
        # Note the loop starts after the first page and before the last page
        for ($i = $loopStart; $i < $loopEnd; $i++) {
            $class = $i === $options['current'] ? 'active' : '';
            $link  = $i === $options['current'] ? '#' : $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.($i).DIRECTORY_SEPARATOR;
            $replacements = [$class, $link, $i];
            $pagination  .= preg_replace($patterns, $replacements, $options['format']);
            $replacements = [];
        }

        # Show the back ellipsis
        $pagination .= $backEllipsis;

        # Show the last page
        $class = $options['current'] === $options['total'] ? 'active' : '';
        $link  = $options['current'] === $options['total'] ? '#' : $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.$options['total'].DIRECTORY_SEPARATOR;
        $replacements = [$class, $link, $options['total']];
        $pagination  .= preg_replace($patterns, $replacements, $options['format']);
        $replacements = [];

        # If show next
        if ($options['prev_next'] === true) {
            $class = $options['current'] <  $options['total'] ? '' : 'disabled' ;

            $link  = $options['current'] <  $options['total'] ? $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.($options['current']+1).DIRECTORY_SEPARATOR : '#';
            $replacements = [$class, $link, $options['next_text']];
            $pagination  .= preg_replace($patterns, $replacements, $options['format']);
        }

        return $pagination;

    }

    /**
     * Get posts archived by year, month
     *
     * @return  array
     */
    public function get_archives()
    {
        # Get all the posts

        $archive  = [];
        $queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC';
        $parser    = new QueryParser($queryStr);
        $posts     = $parser->parseQuery($queryStr);
        if (empty($posts)) return [];
        foreach($posts as $post) {
            $year  = date('Y', $post['created']);
            $month = date('F', $post['created']);
            $archive[$year][$month][] = $post;
        }

        return $archive;
    }

    /**
     * Get theme search form
     *
     * @param  string
     */
    public function get_search_form() 
    {
        # Load from template if it exists
        $formTemplate = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.\Kanso\Kanso::getInstance()->Config()['KANSO_THEME_NAME'].DIRECTORY_SEPARATOR.'searchform.php';
        if (file_exists($formTemplate)) return $this->include_template('searchform');
        
        return '

            <form role="search" method="get" action="'.$this->home_url().'/search-results/">

                <fieldset>
                        
                        <label class="col-3" for="q">Search: </label>
                        
                        <input type="search" name="q" id="q" placeholder="Search...">

                        <button type"submit" class="button">Search</button>

                </fieldset>
                
            </form>

        ';
    }

    /**
     * Recursively build HTML comments (used internally)
     * @return array
     */
    private function commentToString($comments, $options, $permalink, $isChild = false) 
    {

        $HTML         = '';

        foreach ($comments as $comment) {
            
            $patterns     = [];
            $replacements = [];

            $commentStr = $options['format'];

            # Replace classnames
            foreach ($options['classes'] as $suffix => $classname) {
                $patterns[]     = '/\(:classes_'.$suffix.'\)/';
                $class          = 'class="'.$classname;
                if ($suffix === 'wrap' && $isChild) $class .= ' '.$options['classes']['child_wrap'];
                if ($suffix === 'children_wrap' && empty($comment['children'])) $class .= ' '.$options['classes']['no_children'];
                $replacements[] = $class.'"';
            }

            # Replace ID
            $patterns[]     = '/\(:id\)/';
            $replacements[] = $comment['id'];

            # Replace avatar src
            $patterns[]     = '/\(:avatar_src\)/';
            $replacements[] = $this->get_avatar($comment['email'], $options['avatar_size'], true);

            # Replace avatar size
            $patterns[]     = '/\(:avatar_size\)/';
            $replacements[] =  $options['avatar_size'];

            # Replace comment author name
            $patterns[]     = '/\(:comment_name\)/';
            $replacements[] = $comment['name'];

            # Replace Link text
            $patterns[]     = '/\(:link_text\)/';
            $replacements[] = $options['link_text'];

            # Replace time text
            $patterns[]     = '/\(:comment_time_GMT\)/';
            $replacements[] = date("c", $comment['date']);

            $patterns[]     = '/\(:comment_time_format\)/';
            $replacements[] = date($options['time_format'], $comment['date']);

            # Replace content
            $patterns[]     = '/\(:comment_content\)/';
            $replacements[] = $comment['content'];

            # Replace permalinks
            $patterns[]     = '/\(:permalink\)/';
            $replacements[] = $permalink;
            
            $commentStr = preg_replace($patterns, $replacements, $commentStr);

            if (!empty($comment['children'])) {
                
                $commentStr  = preg_replace( '/\(:children\)/',  $this->commentToString($comment['children'], $options, $permalink, true), $commentStr);
            }
            else {

                $commentStr = preg_replace( '/\(:children\)/', '', $commentStr);

            }
            
            $HTML .= $commentStr;
        }
       
       return $HTML;

    }

    /**
     * Recursively build comment tree (used internally)
     *
     * @param  array       $comments
     * @param  int         $parent_id
     * @return array
     */
    private function buildCommentTree($comments, $parent_id = 0)
    {
        $branch = [];
    
        foreach ($comments as $i => $comment) {
            if ($comment['parent'] == $parent_id) {
                unset($comments[$i]);
                $comment['children'] = $this->buildCommentTree($comments, $comment['id']);
                $branch[] = $comment;
            }
        }
    
        return $branch;
    }

    /**
     * Find the next post (used internally)
     *
     * @param   int     $post_id
     * @param   array   $post
     * @return  array|false
     */
    private function findNextPost($post)
    {
        $next = \Kanso\Kanso::getInstance()->CRUD()->SELECT('id')->FROM('posts')->WHERE('created', '>=', $post['created'])->AND_WHERE('status', '=', 'published')->FIND_ALL();
        if (!empty($next)) {
            foreach ($next as $i => $nextPost) {
                if ($nextPost['id'] === $post['id']) {
                    if (isset($next[$i+1])) {
                        return $this->getPostByID($next[$i+1]['id']);
                    }
                }
            }
        }
        return false;
    }


    /**
     * Find the previous post (used internally)
     *
     * @param   int     $post_id
     * @param   array   $post
     * @return  array|false
     */
    private function findPrevPost($post)
    {
        $next = \Kanso\Kanso::getInstance()->CRUD()->SELECT('id')->FROM('posts')->WHERE('created', '<=', $post['created'])->AND_WHERE('status', '=', 'published')->FIND_ALL();
        if (!empty($next)) {
            $next = array_reverse($next);
            foreach ($next as $i => $nextPost) {
                if ($nextPost['id'] === $post['id']) {
                    if (isset($next[$i+1])) {
                        return $this->getPostByID($next[$i+1]['id']);
                    }
                }
            }
        }
        return false;
    }

    /**
     * is string a valid md5 hash
     * @param  string   $md5  md5 hash
     * @return bool   
     */
    private function isValidMd5($md5 ='')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    private function getPostByID($post_id)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->getArticlesByIndex('id', $post_id, 1);
    }

    private function getPostContent($post_id)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('content')->FROM('content_to_posts')->WHERE('post_id', '=', $post_id)->LIMIT(1)->FIND();
    }

    private function getAuthorById($author_id)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('authors')->WHERE('id', '=', $author_id)->FIND();
    }

    private function getAuthorByName($author_name)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('authors')->WHERE('name', '=', $author_name)->LIMIT(1)->FIND();
    }

    private function getTagById($tag_id)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('tags')->WHERE('id', '=', $tag_id)->LIMIT(1)->FIND();
    }

    private function getTagByName($tag_name)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('tags')->WHERE('name', '=', $tag_name)->LIMIT(1)->FIND();
    }

    private function getCategoryById($category_id)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('categories')->WHERE('id', '=', $category_id)->LIMIT(1)->FIND();
    }

    private function getCategoryByName($category_name)
    {
        return \Kanso\Kanso::getInstance()->CRUD()->SELECT('*')->FROM('categories')->WHERE('name', '=', $category_name)->LIMIT(1)->FIND();
    }

}