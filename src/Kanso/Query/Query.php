<?php

namespace Kanso\Query;

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
    public $requestType = 'custom';

    /**
     * @var    string    The string-query to use on the database
     */
    public $queryStr;

    /**
     * @var    int    Current page request if it exists
     */
    public $pageIndex = 0;

    /**
     * @var    int    Current post index of paginated array of posts
     */
    public $postIndex = -1;

    /**
     * @var    int    Current post count
     */
    public $postCount = 0;

    /**
     * @var    array    Array of posts from query result
     */
    public $posts = [];

    /**
     * @var    array    The current post
     */
    public $post = null;

    /**
     * @var    Was the route matched ?
     */
    public $matchedRoute;

    /**
     * @var    string    Current taxonomy slug if applicable (e.g tag, category, author)
     */
    protected $taxonomySlug;

    /**
     * @var    string    Search term if applicable
     */
    protected $searchQuery;

    /**
     * @var    array     array of previously called methods and results
     */
    protected $methodCache = [];

    /**
     * @var    \Kanso\Kanso::getInstance()->Database->Builder()
     */
    protected $SQL;

    /**
     * Constructor
     *
     * @param  string $queryStr       The string-query to use on the database
     * @param  string $requestType    Associative array of data made available to the view (optional)
     * @param  int    $pageIndex      The current page index e.g https://example.com/page3/
     */
    public function __construct($queryStr = '')
    {

        # Set the index
        $this->pageIndex    = \Kanso\Kanso::getInstance()->Request()->fetch('page');
        $this->pageIndex    = $this->pageIndex === 1 || $this->pageIndex === 0 ? 0 : $this->pageIndex-1;
        $this->queryStr     = trim($queryStr);

         # Get an SQL query builder
        $this->SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        # Filter the posts directly from the constructor if
        # this is a custom Query request
        if (!empty($queryStr)) {
            $parser          = new QueryParser();
            $this->posts     = $parser->parseQuery($queryStr);
            $this->postCount = count($this->posts);
        }

    }

    /**
     * Set to not found
     *
     */
    public function setNotFound()
    {
        $this->pageIndex    = 0;
        $this->postIndex    = -1;
        $this->postCount    = 0;
        $this->posts        = [];
        $this->requestType  = NULL;
        $this->queryStr     = NULL;
        $this->post         = NULL;
        $this->taxonomySlug = NULL;
        $this->searchQuery  = NULL;
    }

    /**
     * Filter posts based on a request type
     *
     * @param  string $requestType    The requested page type (optional)
     */
    public function filterPosts($requestType)
    {
        # We also need to set Kanso's response encase it was set to a 404 by 
        # the previous filter
        \Kanso\Kanso::getInstance()->Response->setStatus(200);

        # Load the query parser
        $parser = new QueryParser();

        # Shared values
        $posts        = [];
        $queryStr     = '';
        $postCount    = 0;
        $perPage      = \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE'];
        $offset       = $this->pageIndex * $perPage;
        $limit        = $perPage;
        $taxonomySlug = NULL;
        $uri          = rtrim(\Kanso\Kanso::getInstance()->Environment()['REQUEST_URI'], '/');

        # Filter and paginate the posts based on the request type
        if ($requestType === 'home') {

            $queryStr  = "post_status = published : post_type = post : orderBy = post_created, DESC : limit = $offset, $perPage";
            $posts     = $parser->parseQuery($queryStr);
            $postCount = count($posts);

            # Double check if the tag exists and 404 if it does NOT 
            if (empty($posts)) {
                return \Kanso\Kanso::getInstance()->notFound();
            }

        }
        else if ($requestType === 'archive') {
            $queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC';
            $posts     = $parser->parseQuery($queryStr);
            $postCount = count($posts);
        }
        else if ($requestType === 'tag') {
            $queryStr = 'post_status = published : post_type = post : orderBy = post_created, DESC : tag_slug = '.explode("/", $uri)[2]." : limit = $offset, $perPage";
            $posts    = $parser->parseQuery($queryStr);
            $postCount = count($posts);
            $taxonomySlug = explode("/", $uri)[2];

            # Double check if the tag exists
            # and 404 if it does NOT 
            if (empty($posts)) {
                if (!$this->SQL->SELECT('id')->FROM('tags')->WHERE('slug', '=', explode("/", $uri)[2])->ROW()) {
                    return \Kanso\Kanso::getInstance()->notFound();
                }
            }
            
        }
        else if ($requestType === 'category') {
            $queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC : category_slug = '.explode("/", $uri)[2]." : limit = $offset, $perPage";
            $posts     = $parser->parseQuery($queryStr);
            $postCount = count($posts);
            $taxonomySlug = explode("/", $uri)[2];

            # Double check if the tag exists
            # and 404 if it does NOT 
            if (empty($posts)) {
                if (!$this->SQL->SELECT('id')->FROM('categories')->WHERE('slug', '=', explode("/", $uri)[2])->ROW()) {
                    return \Kanso\Kanso::getInstance()->notFound();
                }
            }
            
        } 
        else if ($requestType === 'author') {
            $queryStr  = ' post_status = published : post_type = post : orderBy = post_created, DESC: author_slug = '.explode("/", $uri)[2].": limit = $offset, $perPage";
            $posts     = $parser->parseQuery($queryStr);
            $postCount = count($posts);
            $taxonomySlug = explode("/", $uri)[2];

            # Double check if the author exists
            # and that they are an admin or writer
            $role = $this->SQL->SELECT('role')->FROM('users')->WHERE('slug', '=', explode("/", $uri)[2])->ROW();
            if ($role) {
                if ($role['role'] !== 'administrator' && $role['role'] !== 'writer') {
                    return \Kanso\Kanso::getInstance()->notFound();
                }
            }
            else {
                return \Kanso\Kanso::getInstance()->notFound();
            }
            
        }
        else if ($requestType === 'single' || \Kanso\Utility\Str::getBeforeFirstChar($requestType, '-') === 'single') {
            $postType = $requestType === 'single' ? 'post' : \Kanso\Utility\Str::getAfterFirstChar($requestType, '-'); 
            if (strpos($uri,'?draft') !== false) {
                $uri = ltrim(str_replace('?draft', '', $uri), '/');
                $queryStr  = 'post_status = draft : post_type = '.$postType.' : post_slug = '.$uri.'/';
                $posts     = $parser->parseQuery($queryStr);
                $postCount = count($posts);
            }
            else {
                $uri       = \Kanso\Utility\Str::GetBeforeLastWord($uri, '/feed');
                $uri       = ltrim($uri, '/');
                $queryStr  = 'post_status = published : post_type = '.$postType.' : post_slug = '.$uri.'/';
                $posts     = $parser->parseQuery($queryStr);
                $postCount = count($posts);
            }

            if (empty($posts)) {
                return \Kanso\Kanso::getInstance()->notFound();
            }
        }
        else if ($requestType === 'page') {
            if (strpos($uri,'?draft') !== false) {
                $uri = ltrim(str_replace('?draft', '', $uri), '/');
                $queryStr  = 'post_status = draft : post_type = page : post_slug = '.$uri.'/';
                $posts     = $parser->parseQuery($queryStr);
                $postCount = count($posts);
            }
            else {
                $uri = \Kanso\Utility\Str::GetBeforeLastWord($uri, '/feed');
                $uri = ltrim($uri, '/');
                $queryStr  = 'post_status = published : post_type = page : post_slug = '.$uri.'/';
                $posts     = $parser->parseQuery($queryStr);
                $postCount = count($posts);
            }

            if (empty($posts)) {
                return \Kanso\Kanso::getInstance()->notFound();
            }
        }
        else if ($requestType === 'search') {

            # Get the query
            $query = \Kanso\Kanso::getInstance()->Request()->fetch('query');
            
            # Validate the query exists
            if (!$query || empty(trim($query))) return;

            # Get the actual search query | sanitize
            $query = htmlspecialchars(trim(strtolower(urldecode(\Kanso\Utility\Str::getAfterLastChar($uri, '=')))));
            $query = \Kanso\Utility\Str::getBeforeFirstChar($query, '/');

            # No need to query empty strings
            if (empty($query)) return;

            # Filter the posts
            $queryStr  = "post_status = published : post_type = post : orderBy = post_created, DESC : post_title LIKE $query || post_excerpt LIKE $query : limit = $offset, $perPage";
            $posts     = $parser->parseQuery($queryStr);
            $postCount = count($posts);
            $this->searchQuery = $query;
        }

        # Set the_post so we're looking at the first item
        if (isset($posts[0])) $this->post = $posts[0];

        # Set values
        $this->posts        = $posts;
        $this->queryStr     = $queryStr;
        $this->postCount    = $postCount;
        $this->requestType  = $requestType;
        $this->taxonomySlug = !$taxonomySlug ? NULL : $taxonomySlug;
        $this->matchedRoute = true;

    }

    /**
     * Add a method result to the cache
     *
     * @param   string    $key
     * @param   mixed     $value
     */
    private function cachePut($key, $value)
    {
        $this->methodCache[$key] = $value;
        return $value;
    }

    /**
     * Get a method result to the cache
     *
     * @param    string    $key
     * @return   mixed
     */
    private function cacheGet($key)
    {
        if ($this->cacheHas($key)) return $this->methodCache[$key];
    }

    /**
     * Remove a method result from the cache
     *
     * @param   string    $key
     */
    private function cacheRemove($key)
    {
        if ($this->cacheHas($key)) unset($this->methodCache[$key]);
    }

    /**
     * Check if a method result exists in the cache
     *
     * @param    string    $key
     * @return   boolean
     */
    private function cacheHas($key)
    {
        return array_key_exists($key, $this->methodCache);
    }

    /**
     * Hash a key for the cache
     *
     * @param    string    $key
     * @return   string
     */
    private function cacheKey($func, $arg_list = [], $numargs = 0)
    {
        $key = $func;
        for ($i = 0; $i < $numargs; $i++) {
            $key .= $i.':'.serialize($arg_list[$i]).';';
        }
        return md5($key);
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
        return !empty($this->SQL->SELECT('id')->FROM('tags')->WHERE($index, '=', $tag_name)->FIND());
    }

    /**
     * Author exists
     *
     * @param   string|int    $author_name 
     * @return  bool  
     */
    public function author_exists($author_name)
    {
        $index       = is_numeric($author_name) ? 'id' : 'username';
        $author_name = is_numeric($author_name) ? intval($author_name) : $author_name;
        $row         = $this->SQL->SELECT('id')->FROM('users')->WHERE($index, '=', $author_name)->ROW();
        if ($row) return $row['role'] === 'administrator' || $row['role'] === 'writer';
        return false;
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
        return !empty($this->SQL->SELECT('id')->FROM('categories')->WHERE($index, '=', $category_name)->FIND());
    }

    /**
     * The post
     *
     * Increment the internal pointer by 1 and return the current post 
     * or just return a single post from the database by id
     * @param   int    $post_id (optional) 
     * @return  Kanso\Articles\Article|false 
     */
    public function the_post($post_id = null)
    {
        if ($post_id) return $this->getPostByID($post_id);
        return $this->_next();
    }

    /**
     * Get all the posts from the current query
     *
     * @return  array
     */
    public function the_posts()
    {
        return $this->posts;
    }

    /**
     * The title
     *
     * @param   int    $post_id (optional) 
     * @return  string|false
     */
    public function the_title($post_id = null)
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->title;
            return false;
        }
        if (!empty($this->post)) return $this->post->title;
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/'.trim($post->slug, '/').'/';
            return false;
        }
        if (!empty($this->post)) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/'.trim($this->post->slug, '/').'/';
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return trim($post->slug, '/').'/';
            return false;
        }
        if (!empty($this->post)) return trim($this->post->slug, '/').'/';
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return htmlspecialchars_decode($post->excerpt);
            return false;
        }
        if (!empty($this->post)) return htmlspecialchars_decode($this->post->excerpt);
        return false;
    }

    /**
     * The category
     *
     * @param   int    $post_id (optional) 
     * @return  array|false
     */
    public function the_category($post_id = null)
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->category;
            return false;
        }
        if (!empty($this->post)) return $this->post->category;
        return false;
    }

    /**
     * The category
     *
     * @param   int    $post_id (optional) 
     * @return  string|false
     */
    public function the_category_name($post_id = null)
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->category['name'];
            return false;
        }
        if (!empty($this->post)) return $this->post->category['name'];
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
            if (!empty($this->post)) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/category/'.$this->post->category['slug'].'/';
            return false;
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
            if (!empty($this->post)) return $this->post->category['slug'];
            return false;
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
            if (!empty($this->post)) return $this->post->category['id'];
            return false;
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post['tags'];
            return [];
        }
        if (!empty($this->post)) return $this->post->tags;
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return \Kanso\Utility\Arr::implodeByKey('name', $post->tags, ', ');
            return '';
        }
        if (!empty($this->post)) return \Kanso\Utility\Arr::implodeByKey('name', $this->post->tags, ', ');
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
     * Get the current taxonomy
     *
     * @param   int   $tag_id 
     * @return  array|false
     */
    public function the_taxonomy() 
    {
        $table = false;
        if ($this->requestType === 'category') {
            $table = 'categories';
        }
        else if ($this->requestType === 'tag') {
            $table = 'tags';
        }
        else if ($this->requestType === 'author') {
            $table = 'users';
        }
        if ($table) {
            return $this->SQL->SELECT('*')->FROM($table)->WHERE('slug', '=', $this->taxonomySlug)->ROW();
        }
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
        $content = '';

        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) $content = $post->content;
        }
        else {
            if (!empty($this->post)) $content = $this->post->content;
        }
        if (empty($content)) return '';

        return \Kanso\Kanso::getInstance()->Markdown->text($content);
    
    }
    
    /**
     * The post thumbnail
     *
     * @param   string   $size    (optional) "small/medium/large"
     * @param   int      $post_id (optional)
     * @return  string|false
     */
    public function the_post_thumbnail($post_id = null) 
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $this->getMediaById($post->thumbnail_id);
            return false;
        }
        if (!empty($this->post)) return $this->getMediaById($this->post->thumbnail_id);
        
        return false;
    }

    /**
     * The post thumbnail
     *
     * @param   string   $size    (optional) "small/medium/large" false for original image
     * @param   int      $post_id (optional)
     * @return  string|false
     */
    public function the_post_thumbnail_src($post_id = null, $size = false) 
    {
        $thumbnail = $this->the_post_thumbnail($post_id);
        if ($thumbnail) {
            return $this->media_size($thumbnail->url, $size);
        }
        return false;
    }

    /**
     * The post thumbnail
     *
     * @param   \Kanso\Media\Attachment $thumbnail
     * @return  string
     */
    public function display_thumbnail($thumbnail, $size = 'original', $width = '', $height = '', $classes = '', $id = '') 
    {
        $width    = !$width ? '' : 'width="'.$width.'"';
        $height   = !$height ? '' : 'height="'.$height.'"';
        $classes  = !$classes ? '' : 'class="'.$classes.'"';
        $id       = !$id ? '' : 'id="'.$id.'"';
        if (!$thumbnail) return '<img src="_" '.$width.' '.$height.' '.$classes.' '.$id.' rel="" alt="" title="">';
        
        $src = $this->media_size($thumbnail->url, $size);
        return '<img src="'.$src.'" '.$width.' '.$height.' '.$classes.' '.$id.' rel="'.$thumbnail->rel.'" alt="'.$thumbnail->alt.'" title="'.$thumbnail->title.'" >';
    }

    private function media_size($url, $size = false)
    {
        if (!$size || $size === 'original') return $url;
        $name = \Kanso\Utility\Str::getBeforeLastChar($url, '.');
        $ext  = \Kanso\Utility\Str::getAfterLastChar($url, '.');
        return $name.'_'.$size.'.'.$ext;
    }

    /**
     * The author
     *
     * @param   int      $post_id (optional)
     * @return  \Kanso\Auth\Adapters\User|false
     */
    public function the_author($post_id = null) 
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $this->getAuthorById($post->author_id);
            return false;
        }
        if (!empty($this->post)) return $this->getAuthorById($this->post->author_id);
        return false;
    }

    /**
     * The author name
     *
     * @param   int      $post_id (optional)
     * @return  string|false
     */
    public function the_author_name($post_id = null) 
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) {
                $author = $this->getAuthorById($post->author_id);
                if ($author) return $author->name;
            }
            return false;
        }
        if (!empty($this->post)) {
            $author = $this->getAuthorById($this->post->author_id);
            if ($author) return $author->name;
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
            if ($author) return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/authors/'.$author->slug;
            return false;
        }
        if (!empty($this->post)) {
            $author = $this->getAuthorById($this->post->author_id);
            return \Kanso\Kanso::getInstance()->Environment()['HTTP_HOST'].'/authors/'.$author->slug;
        }
        return false;
    }

    /**
     * The author thumbnail 
     *
     * @param   string   $size      (optional) "small/medium/large"
     * @param   int      $author_id (optional)
     * @return  object|false
     */
    public function the_author_thumbnail($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return $this->getMediaById($author->thumbnail_id);
            return false;
        }
        if (!empty($this->post)) {
            $author = $this->getAuthorById($this->post->author_id);
            if ($author) return $this->getMediaById($author->thumbnail_id);
            return false;
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
            if ($author) return $author->description;
            return false;
        }
        if (!empty($this->post)) {
            return $this->post->author->description;
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
            if ($author) return $author->twitter;
            return false;
        }
        if (!empty($this->post)) {
            return $this->post->author->twitter;
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
            if ($author) return $author->gplus;
            return false;
        }
        if (!empty($this->post)) {
            return $this->post->author->gplus;
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
            if ($author) return $author->facebook;
        }
        if (!empty($this->post)) {
            return $this->post->author->facebook;
        }
        return false;
    }

    /**
     * The author instagram 
     *
     * @param   int      $author_id (optional)
     * @return  string|false
     */
    public function the_author_instagram($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return $author->instagram;
        }
        if (!empty($this->post)) {
            return $this->post->author->instagram;
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
        if (!empty($this->post)) return $this->post->id;
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->status;
            return false;
        }
        if (!empty($this->post)) return $this->post->status;
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->type;
            return false;
        }
        if (!empty($this->post)) return $this->post->type;
        return false;
    }

    /**
     * The post meta 
     *
     * @param   int      $post_id (optional)
     * @return  mixed|false
     */
    public function the_post_meta($post_id = null)
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->meta;
            return false;
        }
        if (!empty($this->post)) return $this->post->meta;
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return date($format, $post->created);
            return false;
        }
        if (!empty($this->post)) return date($format, $this->post->created);
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return date($format, $post->modified);
            return false;
        }
        if (!empty($this->post)) return date($format, $this->post->modified);
        return false;
    }

    /**
     * The author posts 
     *
     * @param   int      $author_id
     * @return  array
     */
    public function the_author_posts($author_id, $published = true)
    {
        if ($this->author_exists($author_id)) {
            return \Kanso\Kanso::getInstance()->Bookkeeper->byIndex('author_id', intval($author_id), $published);
        }
        return false;
    }

    /**
     * The category posts 
     *
     * @param   int      $category_id
     * @return  array
     */
    public function the_category_posts($category_id, $published = true)
    {
        if ($this->category_exists($category_id)) {
            return \Kanso\Kanso::getInstance()->Bookkeeper->byIndex('category_id', intval($category_id), $published);
        }
        return false;
    }

    /**
     * The tag posts 
     *
     * @param   int      $tag_id
     * @return  array
     */
    public function the_tag_posts($tag_id, $published = true)
    {
        if ($this->tag_exists($tag_id)) {
            return \Kanso\Kanso::getInstance()->Bookkeeper->byIndex('tags.id', intval($tag_id), $published);
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
     * Is single
     *
     * @return  bool
     */
    public function is_custom_post()
    {
        return \Kanso\Utility\Str::getBeforeFirstChar($this->requestType, '-') === 'single';
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
        return  $this->requestType === 'admin';
    }

    /**
     * Is 404
     *
     * @return  bool
     */
    public function is_not_found()
    {
        return \Kanso\Kanso::getInstance()->Response->getStatus() === 404;
    }

    /**
     * Has post Thumbnail
     *
     * @param   int   $post_id   (optional)
     * @return  bool
     */
    public function has_post_thumbnail($post_id = null)
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return !empty($this->getMediaById($post->thumbnail_id));
            return false;
        }
        if (!empty($this->post)) return !empty($this->getMediaById($this->post->thumbnail_id));
        return false;
    }

    /**
     * Has author Thumbnail
     *
     * @param   int   $author_id   (optional)
     * @return  bool
     */
    public function has_author_thumbnail($author_id = null)
    {
        if ($author_id) {
            $author = $this->getAuthorById($author_id);
            if ($author) return !empty($author_id->thumbnail_id);
            return false;
        }
        if (!empty($this->post)) {
            $author = $this->getAuthorById($this->post->author_id);
            if ($author) return !empty($author_id->thumbnail_id);
        }
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return trim($post->excerpt) !== "";
            return false;
        }
        if (!empty($this->post)) trim($this->post->excerpt) !== "";
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
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) {
                $tags = $post->tags;
                if (count($tags) === 1) {
                    if ($tags[0]['id'] === 1) return false;
                }
                return true;
            }
            return false;
        }
        if (!empty($this->post)) {
            $tags = $this->post->tags;
            if (count($tags) === 1) {
                if ($tags[0]['id'] === 1) return false;
            }
            return true;
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
         if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->category['id'] !== 1;
            return false;
        }
        if (!empty($this->post)) return $this->post->category['id'] !== 1;
        return false;
    }

    /**
     * The next page
     *
     * @return  array|false
     */
    public function the_next_page()
    {
        # Get from the cache
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);

        # There are only next/prev pages for single, tags, category, author, and homepage 
        $validRequests = ['single', 'home', 'tag', 'category', 'author'];
        if (!in_array($this->requestType, $validRequests) && !$this->is_custom_post()) {
            return $this->cachePut($key, false);
        }
        
        # Not found don't bother
        if ($this->is_not_found()) return false;

        # If this is a single or custom post just find the next post
        if ($this->is_single() || $this->is_custom_post()) {
            return $this->cachePut($key, $this->findNextPost($this->post));
        }

        # This must now be a paginated page - tag, category, author or homepage listing
        # Get the current page + posts per page and check if there is a page after that
        $perPage  = \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE'];
        $page     = $this->pageIndex + 1;
        $offset   = $page * $perPage;
        $limit    = 1;
        $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->queryStr);
        $parser   = new QueryParser();
        $posts     = $parser->parseQuery($queryStr);

        if (!empty($posts)) {
            $nextPage   = $this->pageIndex + 2;
            $uri        = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['REQUEST_URI'], '/'));
            $titleBase  = $this->website_title();
            $titlePage  = $nextPage > 1 ? 'Page '.$nextPage.' | ' : '';
            $titleTitle = '';
            if ($this->is_home() ) {
                $slug = 'page/'.$nextPage.'/';
            }
            else if ($this->is_tag() || $this->is_category() || $this->is_author() ) {
                $titleTitle = $this->the_taxonomy()['name']. ' | ';
                $slug       = $uri[0].'/'.$uri[1].'/page/'.$nextPage.'/';
            }
            else if ($this->is_search()) {
                $titleTitle = 'Search Results | ';
                $slug       = $uri[0].'/'.$uri[1].'/page/'.$nextPage.'/';
            }
            return $this->cachePut($key, [
                'title' => $titleTitle.$titlePage.$titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->cachePut($key, false);
    }

    /**
     * The previous page
     *
     * @return  array|false
     */
    public function the_previous_page()
    {
        # Get from the cache
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);

        # There are only next/prev pages for single, tags, category, author, and homepage 
        $validRequests = ['single', 'home', 'tag', 'category', 'author'];
        if (!in_array($this->requestType, $validRequests) && !$this->is_custom_post()) {
            return $this->cachePut($key, false);
        }

        # Not found don't bother
        if ($this->is_not_found()) return false;
        
        # If this is a single or custom post just find the next post
        if ($this->is_single() || $this->is_custom_post()) {
            return $this->cachePut($key, $this->findPrevPost($this->post));
        }

        # This must now be a paginated page - tag, category, author or homepage listing
        # Get the current page + posts per page and check if there is a page before that
        if ($this->pageIndex > 0 ) {
            $perPage  = \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE'];
            $page     = $this->pageIndex - 1;
            $offset   = $page * $perPage;
            $limit    = 1;
            $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->queryStr);
            $parser   = new QueryParser();
            $posts    = $parser->parseQuery($queryStr);
        }
        else {
            $posts = [];
        }
        
        if (!empty($posts)) {
            $prevpage   = $this->pageIndex;
            $uri        = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['REQUEST_URI'], '/'));
            $titleBase  = $this->website_title();
            $titlePage  = $prevpage > 1 ? 'Page '.$prevpage.' | ' : '';
            $titleTitle = '';
            if ($this->is_home() ) {
                $slug = $prevpage > 1 ? 'page/'.$prevpage.'/' : '';
            }
            else if ($this->is_tag() || $this->is_category() || $this->is_author() ) {
                $titleTitle = $this->the_taxonomy()['name'].' | ';
                $slug       = $prevpage > 1 ? $uri[0].'/'.$uri[1].'/page/'.$prevpage.'/' : $uri[0].'/'.$uri[1].'/';
            }
            else if ($this->is_search()) {
                $titleTitle = 'Search Results | ';
                $slug       =  $prevpage > 1 ? $uri[0].'/'.$uri[1].'/page/'.$prevpage.'/' : $uri[0].'/'.$uri[1].'/';
            }
            return $this->cachePut($key, [
                'title' => $titleTitle.$titlePage.$titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->cachePut($key, false);
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
        if ($this->is_search()) return urldecode($this->searchQuery);
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

        return $this->postIndex < $this->postCount -1;

        return false;
    }

    /**
     * Rewind the posts and reset post index.
     *
     */
    public function rewind_posts() {
        $this->postIndex = -1;
        if ($this->postCount > 0 ) $this->post = $this->posts[0];
    }

    /**
     * Next post
     */
    public function _next()
    {
        $this->postIndex++;
        if (isset($this->posts[$this->postIndex])) {
            $this->post = $this->posts[$this->postIndex];
        }
        else {
            $this->post = NULL;
        }
        return $this->post;
    }

    /**
     * Previous post
     */
    public function _previous()
    {
        $this->postIndex--;
        if (isset($this->posts[$this->postIndex])) {
            $this->post = $this->posts[$this->postIndex];
        }
        else {
            $this->post = NULL;
        }
        return $this->post;
    }

    /**
     * All the tags 
     * @return array
     */
    public function all_the_tags()
    {
        return $this->SQL->SELECT('*')->FROM('tags')->FIND_ALL();
    }

    /**
     * All the categories 
     * @return array
     */
    public function all_the_categories()
    {
        return $this->SQL->SELECT('*')->FROM('categories')->FIND_ALL();
    }

    /**
     * All the authors 
     * @return array
     */
    public function all_the_authors($registered = true)
    {
        if ($registered) return $this->SQL->SELECT('*')->FROM('users')->WHERE('status', '=', 'confirmed')->FIND_ALL();
        return $this->SQL->SELECT('*')->FROM('users')->FIND_ALL();
    }

    /**
     * The header
     * @return string
     */
    public function the_header()
    {
        return \Kanso\Kanso::getInstance()->View->display(\Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'header.php');
    }

    /**
     * The footer
     * @return string
     */
    public function the_footer()
    {
        return \Kanso\Kanso::getInstance()->View->display(\Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'footer.php');
    }

    /**
     * The sidebar
     *
     * @return string
     */
    public function the_sidebar()
    {
        return \Kanso\Kanso::getInstance()->View->display(\Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'sidebar.php');
    }

    /**
     * Include a template from current theme
     *
     * @param  string $template_name Name of template in current them
     * @return string
     */
    public function include_template($template_name, $data = null)
    {
        $template = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.$template_name.'.php';
        if (file_exists($template)) {
            return \Kanso\Kanso::getInstance()->View->display($template);
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
        return \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'];
    }

    /**
     * Get the theme url
     *
     * @return string
     */
    public function theme_url() 
    {
        return \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR_URL'];
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
    public function domain_name() 
    {
        return \Kanso\Kanso::getInstance()->Environment()['DOMAIN_NAME'];
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
     * Get the meta description
     *
     * @return string
     */
    public function the_meta_description()
    {
        if ($this->is_not_found()) {
            return 'The page you are looking for could not be found.';
        }
        
        $description = $this->website_description();
        
        if ($this->is_single() || $this->is_page() || $this->is_custom_post()) {
            $description = $this->post->excerpt;
        }
        else if ($this->is_search()) {
            $description = 'Search Results for: '.$this->search_query().' - '.$this->website_title();
        }

        return \Kanso\Utility\Str::reduce($description, 180);
    }

    /**
     * The meta title
     *
     * @return  string
     */
    public function the_meta_title()
    {
        $uri        = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['REQUEST_URI'], '/'));
        $titleBase  = $this->website_title();
        $titlePage  = $this->pageIndex > 0 ? 'Page '.($this->pageIndex+1).' | ' : '';
        $titleTitle = '';

        if ($this->is_not_found()) return 'Page Not Found';

        if ($this->is_single() || $this->is_page() || $this->is_custom_post()) {
            if ($this->have_posts()) {
                $titleTitle = $this->post->title.' | ';
            }
        }
        else if ($this->is_tag() || $this->is_category() || $this->is_author()) {
            $titleTitle = $this->the_taxonomy()['name'].' | ';
        }
        else if ($this->is_search()) {
            $titleTitle = 'Search Results | ';
        }
        else if ($this->is_archive()) {
            $titleTitle = 'Archives | ';
        }


        return  $titleTitle.$titlePage.$titleBase;
    }

    /**
     * Get the canonical link
     *
     * @return  string|false
     */
    public function the_canonical_url()
    {
       
        $page = $this->pageIndex;
        $env  = \Kanso\Kanso::getInstance()->Environment;
        $base = $env['HTTP_HOST'];
        $uri  = explode("/", trim($env['REQUEST_URI'], '/'));
        $slug = '';
        if (!$this->have_posts() || $this->is_not_found()) {
            return $env['HTTP_HOST'].$env['REQUEST_URI'];
        }

        if ($this->is_single() || $this->is_page() || $this->is_custom_post()) {
            $slug = $this->post->slug;
        }
        if ($this->is_home() ) {
            $slug = $page > 1 ? 'page/'.$page.'/' : '';
        }
        else if ($this->is_tag() || $this->is_category() || $this->is_author() ) {
            $slug = $page > 1 ? $uri[0].'/'.$uri[1].'/page/'.$page.'/' : $uri[0].'/'.$uri[1].'/';
        }
        else if ($this->is_search()) {
            $slug = $page > 1 ? $uri[0].'/'.$uri[1].'/page/'.$page.'/' : $uri[0].'/'.$uri[1].'/';
        }
        else {
            return $env['HTTP_HOST'].$env['REQUEST_URI'];
        }
        return "$base/$slug";
    }

    /**
     * Get all static pages
     *
     * @return array
     */
    public function static_pages($published = true) 
    {
        return \Kanso\Kanso::getInstance()->Bookkeeper->byIndex('type', 'page', $published);
    }

    /**
     * Get the currently logged in Kanso user (if any)
     *
     * @return array
     */
    public function user() 
    {
        return \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
    }

    /**
     * Validate that the current user is logged in to Kanso's admin panel
     * @return bool
     */
    public function is_loggedIn() 
    {
        return \Kanso\Kanso::getInstance()->Gatekeeper->isLoggedIn();
    }

    /**
     * Validate that the current user is logged in to Kanso's admin panel
     * @return bool
     */
    public function user_is_admin() 
    {
        return \Kanso\Kanso::getInstance()->Gatekeeper->isAdmin();
    }

    /**
     * Validate that an article has comments enabled or not
     * Or if comments are globally disabled
     * @return bool
     */
    public function comments_open($post_id = null) 
    {
        if (\Kanso\Kanso::getInstance()->Config()['KANSO_COMMENTS_OPEN'] === false) return false;
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return $post->comments_enabled == true;
            return false;
        }
        if (!empty($this->post)) return $this->post->comments_enabled == true;
        return false;
    }

    /**
     * Validate that an article has comments or not
     * @return bool
     */
    public function has_comments($post_id = null) 
    {
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) return !empty($post->comments);
            return false;
        }
        $comments = $this->post->comments;
        if (!empty($this->post)) return !empty($comments);
        return false;
    }

    /**
     * Get a comment count on a given article
     * @return int
     */
    public function comments_number($post_id = null)
    {
        $count = 0;
        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if ($post) {
                $comments = $post->comments;
                foreach ($comments as $comment) {
                    if ($comment['status'] === 'approved') $count++;
                }
            }
            return $count;
        }
        if (!empty($this->post)) {
            $comments = $this->post->comments;
            foreach ($comments as $comment) {
                if ($comment['status'] === 'approved') $count++;
            }
        }
        return $count;
    }

    /**
     * Get a single comment by id
     * @return array
     */
    public function get_comment($comment_id)
    {
        return $this->SQL->SELECT('*')->FROM('comments')->WHERE('id', '=', intVal($comment_id))->ROW();
    }

    /**
     * Get an article's comments  
     * @return array
     */
    public function get_comments($post_id = null, $approvedOnly = true)
    {
        $post = [];

        if ($post_id) {
            $post = $this->getPostByID($post_id);
            if (!$post) return [];
        }
        if (!empty($this->post)) $post = $this->post;
        
        if (empty($post)) return [];
        
        $Query = $this->SQL;
        $Query->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$post->id);
        if ($approvedOnly) $Query->AND_WHERE('status', '=', 'approved');
        return $Query->FIND_ALL();
    }

    /**
     * Display the comments
     *
     * @return int
     */
    public function display_comments($args = null, $post_id = null)
    {

        # Are there comments to loop
        $have_comments = $this->comments_number($post_id) > 0;

        # HTML string
        $HTML = '';

        # If there no comments return empty string
        if (!$have_comments) return $HTML;

        # Save the article row locally
        $post  = !$post_id ? $this->post : $this->getPostByID($post_id);

        # Fallback incase nothing is present
        if (!$post || empty($post)) return '';

        # Save the article permalink locally
        $permalink   = $this->the_permalink($post->id);

        # Default comment format
        $defaultFormat = '
            <div (:classes_wrap) data-comment-id="(:id)" id="comment-(:id)">
                
                <div (:classes_body)>
                    
                    <div (:classes_author_wrap)>
                        <div (:classes_avatar_wrap)>
                            <img alt="" src="(:avatar_src)" (:classes_avatar_img) width="(:avatar_size)" height="(:avatar_size)" />
                        </div>
                        <p (:classes_name)>(:comment_name)</p>
                    </div>

                     <div (:classes_meta)>
                        <a (:classes_link) href="(:permalink)#comment-(:id)">(:link_text)</a>  <time (:classes_time) datetime="(:comment_time_GMT)">(:comment_time_format)</time>  
                    </div>

                    <div (:classes_content)>
                        (:comment_content)
                    </div>

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
                    'wrap'          => 'comment',
                    'body'          => 'comment-body',
                    'avatar_wrap'   => 'comment-avatar-wrap',
                    'avatar_img'    => 'comment-avatar-img',
                    'author_wrap'   => 'comment-author-wrap',
                    'name'          => 'comment-author-name',
                    'link'          => 'comment-link',
                    'time'          => 'comment-time',
                    'content'       => 'comment-content',
                    'meta'          => 'comment-meta',
                    'reply'         => 'comment-reply-link',
                    'children_wrap' => 'comment-chidren',
                    'child_wrap'    => 'child-comment',
                    'no_children'   => 'comment-no-children',
                ],
        ];

        # If options were set, overwrite the dafaults
        if ($args && is_array($args)) $options = array_merge($options, $args);

        # Set the default format if not provided
        if (!$options['format']) $options['format'] = $defaultFormat;
        
        # Get the comments as multi-dimensional array
        $comments = $post->comments;

        # If there was an error retrieving the comments return empty string
        if (empty($comments)) return $HTML;

        # Load from template if it exists
        $formTemplate = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'comments.php';
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
        $formTemplate = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'commentform.php';
        if (file_exists($formTemplate)) return $this->include_template('commentform');
      
        # HTML string
        $HTML = '';

        # Save the article row locally
        $post  = !$post_id ? $this->post : $this->getPostByID($post_id);

        # Fallback incase nothing is present
        if (!$post || empty($post)) return '';

        # Save the article id locally
        $postID = $post->id;

        # Save the article permalink locally
        $permalink   = $this->the_permalink($postID);

        $options = [

            'form_class' => 'comment-form',

            'legend' => '
                <legend>Leave a comment:</legend>
            ',

            'comment_field' => '
                <label for="comment-content">Your comment</label>
                <textarea id="comment-content" type="text" name="content" placeholder="Leave a comment..." autocomplete="off"></textarea>
            ',

            'name_field' => '
                <label for="comment-name">Name:</label>
                <input id="comment-name" type="text" name="name" placeholder="Name (required)" autocomplete="off" />
            ',

            'email_field' => '
                <label for="comment-email">Email:</label>
                <input id="comment-email" type="email" name="email" placeholder="Email (required)" autocomplete="off" />
            ',

            'email_replies_field' => '
                <input id="comment-email-reply" type="checkbox" name="email-reply" /> Notify me of follow-up comments by email:<br>
            ',

            'email_thread_field'  => '
                <input id="comment-email-thread" type="checkbox" name="email-thread" /> Notify me of all comments on this post by email:<br>
            ',

            'post_id_field'  => '
                <input id="comment-postId" type="hidden" name="postID" style="display:none" value="(:postID)" />
            ',

            'reply_id' => '',

            'reply_id_field' => '
                <input id="comment-replyId" type="hidden" name="replyID" style="display:none" value="(:replyID)" />
            ',

            'submit_field'   => '
                <button id="comment-submit" type="submit" value="submit">Submit</button>
            ',
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
     *
     * @param  string      $email_address    The email address or md5 of the current user (optional)
     * @param  int         $size             Image size in px
     * @param  bool        $srcOnly          Should we return only the img src (rather than the actual HTML tag)
     * @return string      user's avatar or default mystery on fallback
     */
    public function get_gravatar($email_or_md5, $size = 160, $srcOnly = false) 
    {

        $isMd5   = $this->isValidMd5($email_or_md5);
        
        $isEmail = !filter_var($email_or_md5, FILTER_VALIDATE_EMAIL) === false;

        $domain = \Kanso\Kanso::getInstance()->Environment()['HTTP_PROTOCOL'] === 'https' ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';

        # If there is an error with the emaill or md5 default to fallback 
        # force a mystery man
        if (!$isMd5 && !$isEmail) {
            if ($srcOnly) return $domain.'/avatar/0?s='.$size.'&d=mm';
            return '<img src="'.$domain.'/avatar/0?s='.$size.'&d=mm"/>';
        }
        
        if ($isEmail) $md5 = md5( strtolower( trim( $email_or_md5 ) ) );
        if ($isMd5)   $md5 = $email_or_md5;
       
        if ($srcOnly) return $domain.'/avatar/'.$md5.'?s='.$size.'&d=mm';
        return '<img src="'.$domain.'/avatar/'.$md5.'?s='.$size.'&d=mm"/>';
    
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
          'format_disabled'    => '<li class="(:class)"><span>(:num)</span></li>',
          'white_space'        => " ",
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
        $uri = explode("/", trim(\Kanso\Kanso::getInstance()->Environment()['REQUEST_URI'], '/'));

        # Declare the pagination string
        $pagination = '';

        # Replace the query and create a new one to get the post count;
        $queryStr  = preg_replace('/limit =[^:]+:/', '', $this->queryStr);
        $queryStr  = trim(preg_replace('/limit =[^:]+/', '', $queryStr));
        $queryStr  = trim($queryStr, ":");

        # Empty search has no pages and posts
        if (!empty($queryStr)) {
            # Load the query parser
            $parser = new QueryParser();

            # Count the posts
            $posts = $parser->countQuery($queryStr);
            $pages = \Kanso\Utility\Arr::paginate($posts, $this->pageIndex, \Kanso\Kanso::getInstance()->Config()['KANSO_POSTS_PER_PAGE']);
        }
        else {
            $posts = 0;
            $pages = [];
        }

        # If no args were defined, Kanso will figure it out for us
        if (!$args || !isset($args['current']) || !isset($args['total'])) {
            # pages here are used as for an array so +1 
            $options['current'] = $this->pageIndex === 0 ? 1 : $this->pageIndex+1;
            $options['total']   = count($pages);
        }

        # If options were set, overwrite the dafaults
        if ($args) $options = array_merge($options, $args);

        # Special case if there is only 1 page
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
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[0].DIRECTORY_SEPARATOR.$uri[1];
        }
        else if ($this->is_category()) {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[0].DIRECTORY_SEPARATOR.$uri[1];
        }
        else if ($this->is_author()) {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[0].DIRECTORY_SEPARATOR.$uri[1];
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
            $class  = $options['current'] === 1  ? 'disabled' : '';
            $link   = $options['current'] === 1  ? '#' : $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.($options['current']-1).DIRECTORY_SEPARATOR;
            $link   = $options['current'] === 2  ? $options['base'] : $link;
            $format = $options['current'] === 1  ? $options['format_disabled'] : $options['format'];
            $replacements = [$class, $link, $options['prev_text']];
            $pagination  .= preg_replace($patterns, $replacements, $format).$options['white_space'];
            $replacements = [];
        }

        # Show the first page
        $class = $options['current'] === 1  ? 'active' : '';
        $link  = $options['current'] === 1  ? '#' : $options['base'];
        $replacements = [$class, $link, 1];
        $pagination  .= preg_replace($patterns, $replacements, $options['format']).$options['white_space'];
        $replacements = [];

        # Show the front ellipsis
        $pagination .= $frontEllipsis;

        # Loop over the pages
        # Note the loop starts after the first page and before the last page
        for ($i = $loopStart; $i < $loopEnd; $i++) {
            $class = $i === $options['current'] ? 'active' : '';
            $link  = $i === $options['current'] ? '#' : $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.($i).DIRECTORY_SEPARATOR;
            $replacements = [$class, $link, $i];
            $pagination  .= preg_replace($patterns, $replacements, $options['format']).$options['white_space'];
            $replacements = [];
        }

        # Show the back ellipsis
        $pagination .= $backEllipsis.$options['white_space'];

        # Show the last page
        $class = $options['current'] === $options['total'] ? 'active' : '';
        $link  = $options['current'] === $options['total'] ? '#' : $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.$options['total'].DIRECTORY_SEPARATOR;
        $replacements = [$class, $link, $options['total']];
        $pagination  .= preg_replace($patterns, $replacements, $options['format']).$options['white_space'];
        $replacements = [];

        # If show next
        if ($options['prev_next'] === true) {
            $class  = $options['current'] <  $options['total'] ? '' : 'disabled' ;
            $format = $options['current'] <  $options['total'] ? $options['format'] : $options['format_disabled'] ;
            $link   = $options['current'] <  $options['total'] ? $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.($options['current']+1).DIRECTORY_SEPARATOR : '#';
            $replacements = [$class, $link, $options['next_text']];
            $pagination  .= preg_replace($patterns, $replacements, $format).$options['white_space'];
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
        $archive  = [];

        if ($this->is_archive() ) {
            $posts = $this->posts;
        }
        else {
            $queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC';
            $parser    = new QueryParser($queryStr);
            $posts     = $parser->parseQuery($queryStr);
        }

        if (empty($posts)) return [];

        foreach($posts as $post) {
            $year  = date('Y', $post->created);
            $month = date('F', $post->created);
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
        $formTemplate = \Kanso\Kanso::getInstance()->Environment()['KANSO_THEME_DIR'].DIRECTORY_SEPARATOR.'searchform.php';
        if (file_exists($formTemplate)) return $this->include_template('searchform');
        
        return '

            <form role="search" method="get" action="'.$this->home_url().'/search-results/">

                <fieldset>
                        
                        <label for="q">Search: </label>
                        
                        <input type="search" name="q" id="q" placeholder="Search...">

                        <button type"submit">Search</button>

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
            $replacements[] = $this->get_gravatar($comment['email'], $options['avatar_size'], true);

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
     * @return  array|false
     */
    private function findNextPost($post)
    {
        if (!$post) return false;
        $next = $this->SQL->SELECT('id')->FROM('posts')->WHERE('created', '>=', $post->created)->AND_WHERE('type', '=', $post->type)->AND_WHERE('status', '=', 'published')->ORDER_BY('created', 'ASC')->FIND_ALL();
        if (!empty($next)) {
            $next = array_values($next);
            foreach ($next as $i => $prevPost) {
                if ((int)$prevPost['id'] === (int)$post->id) {
                    if (isset($next[$i+1])) {
                        return $this->SQL->SELECT('*')->FROM('posts')->AND_WHERE('type', '=', $post->type)->WHERE('id', '=', $next[$i+1]['id'])->ROW();
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
        if (!$post) return false;
        $next = $this->SQL->SELECT('id')->FROM('posts')->WHERE('created', '<=', $post->created)->AND_WHERE('type', '=', $post->type)->AND_WHERE('status', '=', 'published')->ORDER_BY('created', 'DESC')->FIND_ALL();
        if (!empty($next)) {
            $next = array_values($next);
            foreach ($next as $i => $prevPost) {
                if ((int)$prevPost['id'] === (int)$post->id) {
                    if (isset($next[$i+1])) {
                        return $this->SQL->SELECT('*')->FROM('posts')->AND_WHERE('type', '=', $post->type)->WHERE('id', '=', $next[$i+1]['id'])->ROW();
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
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);
        return $this->cachePut($key, \Kanso\Kanso::getInstance()->Bookkeeper->existing($post_id));
    }

    private function getAuthorById($author_id)
    {
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);
        return $this->cachePut($key, \Kanso\Kanso::getInstance()->Gatekeeper->getUserProvider()->byId($author_id));
    }

    private function getTagById($tag_id)
    {
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);
        return $this->cachePut($key, $this->SQL->SELECT('*')->FROM('tags')->WHERE('id', '=', $tag_id)->ROW());
    }

    private function getCategoryById($category_id)
    {
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);
        return $this->cachePut($key, $this->SQL->SELECT('*')->FROM('categories')->WHERE('id', '=', $category_id)->ROW());
    }

    private function getCategoryByName($category_name)
    {
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);
        return $this->cachePut($key,  $this->SQL->SELECT('*')->FROM('categories')->WHERE('name', '=', $category_name)->ROW());
    }

    private function getMediaById($thumb_id)
    {
        $key = $this->cacheKey(__FUNCTION__, func_get_args(), func_num_args());
        if ($this->cacheHas($key)) return $this->cacheGet($key);
        return $this->cachePut($key,  \Kanso\Kanso::getInstance()->MediaLibrary->byId($thumb_id));
    }


}