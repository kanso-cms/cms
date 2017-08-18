<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query;

use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\database\query\Builder;
use kanso\framework\config\Config;
use kanso\framework\utility\Str;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Markdown;
use kanso\cms\query\QueryParser;
use kanso\cms\auth\Gatekeeper;
use kanso\cms\wrappers\managers\CategoryManager;
use kanso\cms\wrappers\managers\TagManager;
use kanso\cms\wrappers\managers\UserManager;
use kanso\cms\wrappers\managers\CommentManager;
use kanso\cms\wrappers\managers\PostManager;
use kanso\cms\wrappers\managers\MediaManager;

/**
 * CMS Query object
 *
 * @author Joe J. Howard
 */
class Query
{
    /**
     * The page request type
     *
     * @var string
     */
    public $requestType = 'custom';

    /**
     * The string-query to use on the database
     *
     * @var string    
     */
    public $queryStr;

    /**
     * Current page request if it exists
     *
     * @var int    
     */
    public $pageIndex = 0;

    /**
     * Current post index of paginated array of posts
     *
     * @var int   
     */
    public $postIndex = -1;

    /**
     * Current post count
     *
     * @var int    
     */
    public $postCount = 0;

    /**
     * Array of posts from query result
     *
     * @var array   
     */
    public $posts = [];

    /**
     * The current post
     *
     * @var array    
     */
    public $post = null;

    /**
     * Current taxonomy slug if applicable (e.g tag, category, author)
     *
     * @var string    
     */
    private $taxonomySlug;

    /**
     * Search term if applicable
     *
     * @var string    
     */
    private $searchQuery;

    /**
     * Array of previously called methods and results
     *
     * @var array     
     */
    private $methodCache = [];

    /**
     * Category manager 
     * 
     * @var \kanso\cms\wrappers\managers\CategoryManager
     */
    private $categoryManager;

    /**
     * Tag manager 
     * 
     * @var \kanso\cms\wrappers\managers\TagManager
     */
    private $tagManager;

    /**
     * User manager 
     * 
     * @var \kanso\cms\wrappers\managers\UserManager
     */
    private $userManager;

    /**
     * Comment manager 
     * 
     * @var \kanso\cms\wrappers\managers\CommentManager
     */
    private $commentManager;

    /**
     * Post manager 
     * 
     * @var \kanso\cms\wrappers\managers\PostManager
     */
    private $postManager;

    /**
     * media manager 
     * 
     * @var \kanso\cms\wrappers\managers\MediaManager
     */
    private $mediaManager;

    /**
     * Request object
     *
     * @var \kanso\framework\http\request\Request
     */
    private $request;

    /**
     * Response object
     *
     * @var \kanso\framework\http\response\Response
     */
    private $response;

    /**
     * SQL query builder instance
     * 
     * @var \kanso\framework\database\query\Builder
     */ 
    private $SQL;

    /**
     * Config 
     * 
     * @var \kanso\framework\config\Config
     */
    private $config;

    /**
     * Config 
     * 
     * @var \kanso\cms\query\QueryParser
     */
    private $queryParser;

    /**
     * Method cache 
     * 
     * @var \kanso\cms\query\Cache
     */
    private $cache;

    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\cms\auth\Gatekeeper                   $gatekeeper      CMS Gatekeeper
     * @param  \kanso\cms\wrappers\managers\CategoryManager $categoryManager Category manager instance
     * @param  \kanso\cms\wrappers\managers\TagManager      $tagManager      Tag manager instance
     * @param  \kanso\cms\wrappers\managers\UserManager     $userManager     User manager instance
     * @param  \kanso\cms\wrappers\managers\CommentManager  $commentManager  Comment manager instance
     * @param  \kanso\cms\wrappers\managers\PostManager     $postManager     Post manager instance
     * @param  \kanso\cms\wrappers\managers\MediaManager    $MediaManager    Media manager instance
     * @param  \kanso\framework\http\request\Request        $request         Request object instance
     * @param  \kanso\framework\http\response\Response      $response        Request object instance
     * @param  \kanso\framework\database\query\Builder      $SQL             SQL query builder
     * @param  \kanso\framework\config\Config               $config          Framework configuration
     * @param  \kanso\cms\query\QueryParser                 $queryParser     Query parser
     * @param  \kanso\cms\query\Cache                       $cache           Method cache
     */
    public function __construct(Gatekeeper $gatekeeper, CategoryManager $categoryManager, TagManager $tagManager, UserManager $userManager, PostManager $postManager, MediaManager $mediaManager, CommentManager $commentManager, Request $request, Response $response, Builder $SQL, Config $config, QueryParser $queryParser, Cache $cache)
    {
        $this->gatekeeper = $gatekeeper;

        $this->categoryManager = $categoryManager;
        
        $this->tagManager = $tagManager;
        
        $this->userManager = $userManager;
        
        $this->postManager = $postManager;
        
        $this->mediaManager = $mediaManager;
        
        $this->commentManager = $commentManager;

        $this->request = $request;

        $this->response = $response;

        $this->SQL = $SQL;

        $this->config = $config;

        $this->queryParser = $queryParser;

        $this->cache = $cache;

        $this->pageIndex = $this->request->fetch('page');
        
        $this->pageIndex = $this->pageIndex === 1 || $this->pageIndex === 0 ? 0 : $this->pageIndex-1;
    }

    /**
     * Create and return a new Query object
     *
     * @access public
     * @param  string queryStr Query to filter posts
     * @return \kanso\cms\Query
     */
    public function create(string $queryStr = ''): Query
    {
        $instance = clone $this;

        $instance->applyQuery($queryStr);

        return $instance;
    }

    /**
     * Apply a query for a custom string
     *
     * @access public
     */
    private function applyQuery($queryStr)
    {
        $this->reset();

        $this->queryStr = trim($queryStr);

        $this->posts = $this->queryParser->parseQuery($queryStr);

        $this->postCount = count($this->posts);

        $this->requestType  = 'custom';

        if (isset($this->posts[0]))
        {
            $this->post = $this->posts[0];
        }
    }

    /**
     * Reset the internal properties to defaults
     *
     * @access public
     */
    public function reset()
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
     * Filter the posts by the request type
     *
     * Note this method is used from the router/CMS core to filter posts based
     * on the matched route.
     *
     * @access public
     * @param  string $requestType The requested page type
     */
    public function filterPosts(string $requestType)
    {
        # Reset the internal properties
        $this->reset();

        # Reset the response to 200
        $this->response->status()->set(200);

        # Shared values
        $posts        = [];
        $queryStr     = '';
        $postCount    = 0;
        $perPage      = $this->config->get('cms.posts_per_page');
        $offset       = $this->pageIndex * $perPage;
        $limit        = $perPage;
        $taxonomySlug = NULL;
        $uri          = rtrim($this->request->path(), '/');

        # Filter and paginate the posts based on the request type
        if ($requestType === 'home')
        {
            $queryStr  = "post_status = published : post_type = post : orderBy = post_created, DESC : limit = $offset, $perPage";
            $posts     = $this->queryParser->parseQuery($queryStr);
            $postCount = count($posts);

            if (empty($posts))
            {
                return $this->response->status()->set(404);
            }

        }
        else if ($requestType === 'tag')
        {
            $queryStr = 'post_status = published : post_type = post : orderBy = post_created, DESC : tag_slug = '.explode("/", $uri)[1]." : limit = $offset, $perPage";
            $posts    = $this->queryParser->parseQuery($queryStr);
            $postCount = count($posts);
            $taxonomySlug = explode("/", $uri)[1];

            if (empty($posts))
            {
                if (!$this->SQL->SELECT('id')->FROM('tags')->WHERE('slug', '=', explode("/", $uri)[1])->ROW())
                {
                    return $this->response->status()->set(404);
                }
            }
            
        }
        else if ($requestType === 'category')
        {
            $queryStr  = 'post_status = published : post_type = post : orderBy = post_created, DESC : category_slug = '.explode("/", $uri)[1]." : limit = $offset, $perPage";
            $posts     = $this->queryParser->parseQuery($queryStr);
            $postCount = count($posts);
            $taxonomySlug = explode("/", $uri)[1];

            # Double check if the tag exists
            # and 404 if it does NOT 
            if (empty($posts))
            {
                if (!$this->SQL->SELECT('id')->FROM('categories')->WHERE('slug', '=', explode("/", $uri)[1])->ROW())
                {
                    return $this->response->status()->set(404);
                }
            }
            
        } 
        else if ($requestType === 'author')
        {
            $queryStr  = ' post_status = published : post_type = post : orderBy = post_created, DESC: author_slug = '.explode("/", $uri)[1].": limit = $offset, $perPage";
            $posts     = $this->queryParser->parseQuery($queryStr);
            $postCount = count($posts);
            $taxonomySlug = explode("/", $uri)[1];

            # Double check if the author exists
            # and that they are an admin or writer
            $role = $this->SQL->SELECT('role')->FROM('users')->WHERE('slug', '=', explode("/", $uri)[1])->ROW();
            if ($role)
            {
                if ($role['role'] !== 'administrator' && $role['role'] !== 'writer')
                {
                    return $this->response->status()->set(404);
                }
            }
            else
            {
                return $this->response->status()->set(404);
            }
            
        }
        else if ($requestType === 'single' || Str::getBeforeFirstChar($requestType, '-') === 'single')
        {
            $postType = $requestType === 'single' ? 'post' : Str::getAfterFirstChar($requestType, '-'); 
            if (strpos($uri,'?draft') !== false)
            {
                $uri = ltrim(str_replace('?draft', '', $uri), '/');
                $queryStr  = 'post_status = draft : post_type = '.$postType.' : post_slug = '.$uri.'/';
                $posts     = $this->queryParser->parseQuery($queryStr);
                $postCount = count($posts);
            }
            else
            {
                $uri       = Str::GetBeforeLastWord($uri, '/feed');
                $uri       = ltrim($uri, '/');
                $queryStr  = 'post_status = published : post_type = '.$postType.' : post_slug = '.$uri.'/';
                $posts     = $this->queryParser->parseQuery($queryStr);
                $postCount = count($posts);
            }

            if (empty($posts))
            {
                return $this->response->status()->set(404);
            }
        }
        else if ($requestType === 'page')
        {
            if (strpos($uri,'?draft') !== false)
            {
                $uri = ltrim(str_replace('?draft', '', $uri), '/');
                $queryStr  = 'post_status = draft : post_type = page : post_slug = '.$uri.'/';
                $posts     = $this->queryParser->parseQuery($queryStr);
                $postCount = count($posts);
            }
            else
            {
                $uri = Str::GetBeforeLastWord($uri, '/feed');
                $uri = ltrim($uri, '/');
                $queryStr  = 'post_status = published : post_type = page : post_slug = '.$uri.'/';
                $posts     = $this->queryParser->parseQuery($queryStr);
                $postCount = count($posts);
            }

            if (empty($posts))
            {
                return $this->response->status()->set(404);
            }
        }
        else if ($requestType === 'search')
        {
            # Get the query
            $query = $this->request->fetch('query');
            
            # Validate the query exists
            if (!$query || empty(trim($query))) return;

            # Get the actual search query | sanitize
            $query = htmlspecialchars(trim(strtolower(urldecode(Str::getAfterLastChar($uri, '=')))));
            $query = Str::getBeforeFirstChar($query, '/');

            # No need to query empty strings
            if (empty($query)) return;

            # Filter the posts
            $queryStr  = "post_status = published : post_type = post : orderBy = post_created, DESC : post_title LIKE $query || post_excerpt LIKE $query : limit = $offset, $perPage";
            $posts     = $this->queryParser->parseQuery($queryStr);
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
    }

    /**
     * Checks whether a given tag exists by the tag name or id.
     *
     * @param   string|integer    $tag_name    Tag name or id
     * @return  boolean
     */
    public function tag_exists($tag_name)
    {
        $index = is_numeric($tag_name) ? 'id' : 'name';
        
        $tag_name = is_numeric($tag_name) ? intval($tag_name) : $tag_name;

        return !empty($this->tagManager->provider()->byKey($index, $tag_name));
    }

    /**
     * Checks whether a given author exists by name or id.
     *
     * @param   string|integer    $author_name    Author name or id
     * @return  boolean
     */
    public function author_exists($author_name)
    {
        $index = is_numeric($author_name) ? 'id' : 'username';
        
        $author_name = is_numeric($author_name) ? intval($author_name) : $author_name;

        $author = $this->userManager->provider()->byKey($index, $tag_name);

        if ($author)
        {
            return $author->role === 'administrator' ||$author->role === 'writer';
        }

        return false;
    }

    /**
     * Checks whether a given author category by name or id.
     *
     * @param   string|integer    $category_name    Category name or id
     * @return  boolean
     */
    public function category_exists($category_name)
    {
        $index = is_numeric($category_name) ? 'id' : 'name';
        
        $category_name = is_numeric($category_name) ? intval($category_name) : $category_name;

        return !empty($this->categoryManager->provider()->byKey($index, $tag_name));
    }

    /**
     * Increment the internal pointer by 1 and return the current post 
     * or just return a single post by id
     *
     * @param   integer     $post_id      (optional) (default NULL)
     * @return  kanso\articles\Article|FALSE
     */
    public function the_post($post_id = null)
    {        
        if ($post_id)
        {
            return $this->getPostByID($post_id);
        }

        return $this->_next();
    }

    /**
     * Get all the posts from the current query
     *
     * @param   NULL
     * @return  array
     */
    public function the_posts()
    {
        return $this->posts;
    }

    /**
     * Get the title of the current post or a post by id
     *
     * @param   integer     $post_id      (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_title($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);

            if ($post)
            {
                return $post->title;
            }
            
            return null;
        }

        if (!empty($this->post))
        {
            return $this->post->title;
        }

        return null;
    }

    /**
     * Get the full URL of the current post or a post by id
     *
     * @param   integer     $post_id      (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_permalink($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);

            if ($post)
            {
                return $this->request->environment()->HTTP_HOST.'/'.trim($post->slug, '/').'/';
            }

            return null;
        }

        if (!empty($this->post))
        {
            return $this->request->environment()->HTTP_HOST.'/'.trim($this->post->slug, '/').'/';
        }

        return null;
    }

    /**
     * Get the slug of the current post or a post by id
     *
     * @param   integer     $post_id      (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_slug($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return trim($post->slug, '/').'/';
            }

            return null;
        }

        if (!empty($this->post))
        {
            return trim($this->post->slug, '/').'/';
        }

        return null;
    }

    /**
     * Get the excerpt of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string|FALSE
     */
    public function the_excerpt($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return trim(strip_tags(Markdown::convert($post->excerpt)));
            }

            return null;
        }
        
        if (!empty($this->post))
        {
            return trim(strip_tags(Markdown::convert($this->post->excerpt)));
        }

        return null;
    }

    /**
     * Get the category array of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  array|FALSE
     */
    public function the_category($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return $post->category;
            }

            return null;
        }
        
        if (!empty($this->post))
        {
            return $this->post->category;
        }

        return null;
    }

    /**
     * Get the category name of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string|FALSE
     */
    public function the_category_name($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return $post->category->name;
            }

            return null;
        }
        
        if (!empty($this->post))
        {
            return $this->post->category->name;
        }

        return null;
    }

    /**
     * Get the full URL of the category of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string|FALSE
     */
    public function the_category_url($category_id = null)
    {
        if (!$category_id)
        {
            if (!empty($this->post))
            {
                return $this->request->environment()->HTTP_HOST.'/category/'.$this->post->category->slug.'/';
            }

            return null;
        }
        else
        {
            $category = $this->getCategoryById($category_id);

            if ($category)
            {
                return $this->request->environment()->HTTP_HOST.'/category/'.$category->slug.'/';
            }
        }

        return null;
    }

    /**
     * Get the category slug of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string|FALSE
     */
    public function the_category_slug($category_id = null)
    {
        if (!$category_id)
        {
            if (!empty($this->post))
            {
                return $this->post->category->slug;
            }

            return null;
        }
        else
        {
            $category = $this->getCategoryById($category_id);
            
            if ($category)
            {
                return $category->slug;
            }
        }

        return null;
    }

    /**
     * Get the category id of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  integer|FALSE
     */
    public function the_category_id($category_name = null)
    {
        if (!$category_name)
        {
            if (!empty($this->post))
            {
                return $this->post->category->id;
            }

            return null;
        }
        else
        {
            $category = $this->categoryManager->byName($category_name);

            if ($category)
            {
                return $category->id;
            }
        }

        return null;
    }

    /**
     * Get an array of tags of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  array
     */
    public function the_tags($post_id = null) 
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            if ($post)
            {
                return $post->tags;
            }
        }
        else if (!empty($this->post))
        {
            return $this->post->tags;
        }

        return [];
    }

    /**
     * Get a comma separated list of the tag names of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string
     */
    public function the_tags_list($post_id = null, $glue = ', ')
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return $this->listTags($post->tags, $glue);
            }
        }
        else if (!empty($this->post))
        {
            return $this->listTags($this->post->tags, $glue);
        }

        return '';
    }

    private function listTags(array $tags, string $glue)
    {
        $str = '';
        
        foreach ($tags as $tag)
        {
            $str .= $tag->name.$glue;
        }
        
        $split = array_filter(explode($glue, $str));
        
        return implode($glue , $split);
    }

    /**
     * Get the slug of a tag by id
     *
     * @param   integer    $tag_id
     * @return  string|FALSE
     */
    public function the_tag_slug($tag_id) 
    {
        $tag = $this->getTagById($tag_id);

        if ($tag)
        {
            return $tag->slug;
        }

        return false;
    }

    /**
     * Get the full URL of a tag by id
     *
     * @param   integer    $tag_id
     * @return  string|FALSE
     */
    public function the_tag_url($tag_id) 
    {
        $tag = $this->getTagById($tag_id);

        if ($tag)
        {
            return $this->request->environment()->HTTP_HOST.'/tag/'.$tag->slug;
        }

        return false;
    }

    /**
     * If the request is for a tag, category or author returns an array of that 
     * taxonomy
     *
     * @param   NULL
     */
    public function the_taxonomy() 
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }
        
        if ($this->requestType === 'category')
        {
            return $this->cache->set($key, $this->categoryManager->provider()->byKey('slug', $this->taxonomySlug, true));
        }
        else if ($this->requestType === 'tag')
        {
            return $this->cache->set($key, $this->tagManager->provider()->byKey('slug', $this->taxonomySlug, true));
        }
        else if ($this->requestType === 'author')
        {
            return $this->cache->set($key, $this->userManager->provider()->byKey('slug', $this->taxonomySlug, true));
        }

        return null;
    }

    /**
     * Gets the HTML content for current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string
     */
    public function the_content($post_id = null) 
    {
        $content = '';

        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                $content = $post->content;
            }
        }
        else
        {
            if (!empty($this->post))
            {
                $content = $this->post->content;
            }
        }

        if (empty($content))
        {
            return '';
        }

        return Markdown::convert($content);
    }
    
    /**
     * Gets an attachment object for the current post or a post by id thumbnail
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  \kanso\media\Attachment|FALSE
     */
    public function the_post_thumbnail($post_id = null) 
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);

            if ($post)
            {
                return $this->getMediaById($post->thumbnail_id);
            }

            return null;
        }
        else if (!empty($this->post))
        {
            return $this->getMediaById($this->post->thumbnail_id);
        }
        
        return null;
    }

    /**
     * Gets the thumbnail src for the current post or a post by id in a given size
     *
     * @param   integer       $post_id (optional) (Default NULL)
     * @param   string    $size    The post thumbnail size "small"|"medium"|"large"|"original" (optional) (Default 'original') 
     * @return  string|FALSE
     */
    public function the_post_thumbnail_src($post_id = null, $size = 'original') 
    {
        $thumbnail = $this->the_post_thumbnail($post_id);
        
        if ($thumbnail)
        {
            return $thumbnail->imgSize($size);
        }

        return null;
    }

    /**
     * Prints an HTML img tag from Kanso attachment object.
     *
     * @param   \kanso\media\Attachment    $thumbnail    The attachment to print
     * @param   string                     $size         The post thumbnail size "small"|"medium"|"large"|"original" (optional) (Default 'original') 
     * @param   string                     $width        The img tag's width attribute  (optional) (Default '') 
     * @param   string                     $height       The img tag's height attribute (optional) (Default '') 
     * @param   string                     $classes      The img tag's class attribute  (optional) (Default '') 
     * @param   string                     $id           The img tag's id attribute (optional) (Default '') 
     * @return  string
     */
    public function display_thumbnail($thumbnail, $size = 'original', $width = '', $height = '', $classes = '', $id = '') 
    {
        $width    = !$width ? '' : 'width="'.$width.'"';
        $height   = !$height ? '' : 'height="'.$height.'"';
        $classes  = !$classes ? '' : 'class="'.$classes.'"';
        $id       = !$id ? '' : 'id="'.$id.'"';
        
        if (!$thumbnail)
        {
            return '<img src="_" '.$width.' '.$height.' '.$classes.' '.$id.' rel="" alt="" title="">';
        }
        
        $src = $thumbnail->imgSize($size);

        return '<img src="'.$src.'" '.$width.' '.$height.' '.$classes.' '.$id.' rel="'.$thumbnail->rel.'" alt="'.$thumbnail->alt.'" title="'.$thumbnail->title.'" >';
    }

    /**
     * Get the author of the current post or a post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  \kanso\auth\adapters\User|FALSE
     */
    public function the_author($post_id = null) 
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return $this->getAuthorById($post->author_id);
            }

            return null;
        }

        if (!empty($this->post))
        {
            return $this->getAuthorById($this->post->author_id);
        }

        return null;
    }

    /**
     * Get the author name of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_author_name($author_id = null) 
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            
            if ($author)
            {
                return $author->name;
            }

            return null;
        }
        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);
            
            if ($author)
            {
                return $author->name;
            }
        }

        return null;
    }

    /**
     * Get the authors full URL of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_author_url($author_id = null)
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            
            if ($author)
            {
                return $this->request->environment()->HTTP_HOST.'/authors/'.$author->slug;
            }

            return null;
        }

        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);

            if ($author)
            {
                return $this->request->environment()->HTTP_HOST.'/authors/'.$author->slug;
            }
        }

        return null;
    }

    /**
     * Get the authors thumbnail attachment of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  \kanso\media\Attachment|FALSE
     */
    public function the_author_thumbnail($author_id = null)
    {        
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);

            if ($author)
            {
                return $this->getMediaById($author->thumbnail_id);
            }

            return null;
        }

        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);

            if ($author)
            {

                return $this->getMediaById($author->thumbnail_id);
            }

            return null;
        }

        return null;
    }

    /**
     * Get the authors bio of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_author_bio($author_id = null)
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            
            if ($author)
            {
                return $author->description;
            }

            return null;
        }

        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);
            
            if ($author)
            {
                return $author->description;
            }

        }
        return null;
    }

    /**
     * Get the authors twitter URL of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_author_twitter($author_id = null)
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            
            if ($author)
            {
                return $author->twitter;
            }

            return null;
        }

        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);
            
            if ($author)
            {
                return $author->twitter;
            }

        }

        return null;
    }

    /**
     * Get the authors google URL of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_author_google($author_id = null)
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            if ($author)
            {
                return $author->gplus;
            }

            return null;
        }
        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);
            
            if ($author)
            {
                return $author->gplus;
            }

        }

        return null;
    }

    /**
     * Get the authors facebook URL of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_author_facebook($author_id = null)
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            
            if ($author)
            {
                return $author->facebook;
            }

            return null;
        }

        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);
            
            if ($author)
            {
                return $author->facebook;
            }
        }

        return null;
    }

    /**
     * Get the authors instagram URL of the current post or an author by id
     *
     * @param   integer     $author_id   (optional) (default NULL)
     * @return  string|FALSE
     */
    public function the_author_instagram($author_id = null)
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            
            if ($author)
            {
                return $author->instagram;
            }

            return false;
        }

        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);
            if ($author)
            {
                return $author->instagram;
            }
        }
        return false;
    }

    /**
     * Get the current post id
     *
     * @return  string|FALSE
     */
    public function the_post_id() 
    {
        if (!empty($this->post))
        {
            return $this->post->id;
        }

        return null;
    }

    /**
     * Get the status of the current post or post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string|FALSE
     */
    public function the_post_status($post_id = null) 
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            if ($post)
            {
                return $post->status;
            }

            return null;
        }
        
        if (!empty($this->post))
        {
            return $this->post->status;
        }

        return null;
    }

    /**
     * Get the type of the current post or post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  string|FALSE
     */
    public function the_post_type($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return $post->type;
            }

            return null;
        }
        
        if (!empty($this->post))
        {
            return $this->post->type;
        }

        return null;
    }

    /**
     * Get the meta for the current post or post by id
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  mixed|FALSE
     */
    public function the_post_meta($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return $post->meta;
            }

            return null;
        }

        if (!empty($this->post))
        {
            return $this->post->meta;
        }
        
        return null;
    }

    /**
     * Get the created time of the current post or a post by id 
     *
     * @param   string       $format  (optional) (Default 'U')
     * @param   integer      $post_id (optional) (Default NULL)
     * @return  string|false
     */
    public function the_time($format = 'U', $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return date($format, $post->created);
            }

            return null;
        }
        
        if (!empty($this->post))
        {
            return date($format, $this->post->created);
        }
        
        return null;
    }

    /**
     * Get the last modified time of the current post or a post by id 
     *
     * @param   string       $format  (optional) (Default 'U')
     * @param   integer      $post_id (optional) (Default NULL)
     * @return  string|false
     */
    public function the_modified_time($format = 'U', $post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return date($format, $post->modified);
            }
            
            return false;
        }
        
        if (!empty($this->post))
        {
            return date($format, $this->post->modified);
        }
        
        return false;
    }

    /**
     * Ge an array of \kanso\articles\Article objects by author id
     *
     * @param   integer      $author_id    The author id
     * @param   boolean      $published    Get only published articles (optional) (Default TRUE)
     * @return  array
     */
    public function the_author_posts($author_id, $published = true)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        if ($this->author_exists($author_id))
        {
            return $this->cache->set($key, $this->postManager->provider()->byKey('posts.author_id', $author_id, false, $published));
        }

        return $this->cache->set($key, []);
    }

    /**
     * Ge an array of \kanso\articles\Article objects by category id
     *
     * @param   integer      $category_id    The category id
     * @param   boolean      $published      Get only published articles (optional) (Default TRUE)
     * @return  array
     */
    public function the_category_posts($category_id, $published = true)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        if ($this->category_exists($category_id))
        {
            return $this->cache->set($key,  $this->postManager->provider()->byKey('posts.category_id', $category_id, false, $published));
        }

        return $this->cache->set($key, []);
    }

    /**
     * Ge an array of \kanso\articles\Article objects by tag id
     *
     * @param   integer      $tag_id       The tag id
     * @param   boolean      $published    Get only published articles (optional) (Default TRUE)
     * @return  array
     */
    public function the_tag_posts($tag_id, $published = true)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        if ($this->tag_exists($tag_id))
        {
            return $this->cache->set($key,  $this->postManager->provider()->byKey('tags.id', $tag_id, false, $published));
        }

        return $this->cache->set($key, []);
    }

    /**
     * Get the current page type
     *
     * @param   NULL
     * @return  string
     */
    public function the_page_type()
    {
        return $this->requestType;
    }

    /**
     * Is this a single request
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_single()
    {
        return $this->requestType === 'single';
    }

    /**
     * Is this a custom post request
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_custom_post()
    {
        return Str::getBeforeFirstChar($this->requestType, '-') === 'single';
    }

    /**
     * Is this a request for the homepage
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_home()
    {
        return $this->requestType === 'home';
    }

    /**
     * Is this the first page of a paginated set of posts ?
     *
     * @param   NULL
     * @return  boolean
     */
    function is_front_page()
    {
       return $this->pageIndex === 0;
    }

    /**
     * Is this a page request ?
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_page()
    {
        return $this->requestType === 'page';
    }

    /**
     * Is this a search results request ?
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_search()
    {
        return $this->requestType === 'search';
    }

   /**
     * Is this a tag request ?
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_tag()
    {
        return $this->requestType === 'tag';
    }

    /**
     * Is this a category request ?
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_category()
    {
        return $this->requestType === 'category';
    }

    /**
     * Is this an author request ?
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_author()
    {
        return $this->requestType === 'author';
    }

    /**
     * Is this an admin request ?
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_admin()
    {
        return  $this->requestType === 'admin';
    }

    /**
     * Is this a 404 request/response ?
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_not_found()
    {
        return $this->response->status()->get() === 404;
    }

    /**
     * Does the current post or a post by id have a thumbnail attachment
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  boolean
     */
    public function has_post_thumbnail($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post && !empty($post->thumbnail_id))
            {
                return !empty($this->getMediaById($post->thumbnail_id));
            }

            return false;
        }

        if (!empty($this->post) && !empty($post->thumbnail_id))
        {
            return !empty($this->getMediaById($this->post->thumbnail_id));
        }

        return false;
    }

    /**
     * Does the author of the current post or an author by id have a thumbnail attachment
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  boolean
     */
    public function has_author_thumbnail($author_id = null)
    {
        if ($author_id)
        {
            $author = $this->getAuthorById($author_id);
            
            if ($author)
            {
                return !empty($this->getMediaById($author_id->thumbnail_id));
            }

            return false;
        }

        if (!empty($this->post))
        {
            $author = $this->getAuthorById($this->post->author_id);
            
            if ($author)
            {
                return !empty($this->getMediaById($author->thumbnail_id));
            }
        }

        return false;
    }

    /**
     * Is the current post or a post by id untagged ?
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  boolean
     */
    public function has_tags($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                $tags = $post->tags;
                
                if (count($tags) === 1)
                {
                    if ($tags[0]->id === 1) return false;
                }
                
                return true;
            }
            
            return false;
        }
        if (!empty($this->post))
        {
            $tags = $this->post->tags;
            
            if (count($tags) === 1)
            {
                if ($tags[0]->id === 1) return false;
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Is the current post or a post by id uncategorized ?
     *
     * @param   integer     $post_id     (optional) (Default NULL)
     * @return  boolean
     */
    public function has_category($post_id = null)
    {
         if ($post_id)
         {
            $post = $this->getPostByID($post_id);
            
            if ($post)
            {
                return $post->category->id !== 1;
            }

            return false;
        }
        
        if (!empty($this->post))
        {
            return $this->post->category->id !== 1;
        }
        
        return false;
    }

    /**
     * Gets an array for the next page returning its title and slug. Works on single, home, author, tag, category requests
     *
     * @param   NULL
     * @return  array|FALSE
     */
    public function the_next_page()
    {
        # Not found don't bother
        if ($this->is_not_found())
        {
            return false;
        }

        # Get for the cache
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        # There are only next/prev pages for single, tags, category, author, and homepage 
        if (!in_array($this->requestType, ['single', 'home', 'tag', 'category', 'author']) && !$this->is_custom_post())
        {
            return $this->cache->set($key, false);
        }
        
        # Load from cache if we can 
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        # If this is a single or custom post just find the next post
        if ($this->is_single() || $this->is_custom_post())
        {
            return $this->cache->set($key, $this->findNextPost($this->post));
        }

        # This must now be a paginated page - tag, category, author or homepage listing
        # Get the current page + posts per page and check if there is a page after that
        $perPage  = $this->config->get('cms.posts_per_page');
        $page     = $this->pageIndex + 1;
        $offset   = $page * $perPage;
        $limit    = 1;
        $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->queryStr);
        $posts     = $this->queryParser->parseQuery($queryStr);

        if (!empty($posts))
        {
            $nextPage   = $this->pageIndex + 2;
            $uri        = explode("/", trim($this->request->environment()->REQUEST_URI, '/'));
            $titleBase  = $this->website_title();
            $titlePage  = $nextPage > 1 ? 'Page '.$nextPage.' | ' : '';
            $titleTitle = '';
            if ($this->is_home() )
            {
                $slug = 'page/'.$nextPage.'/';
            }
            else if ($this->is_tag() || $this->is_category() || $this->is_author() )
            {
                $titleTitle = $this->the_taxonomy()->name. ' | ';
                $slug       = $uri[0].'/'.$uri[1].'/page/'.$nextPage.'/';
            }
            else if ($this->is_search())
            {
                $titleTitle = 'Search Results | ';
                $slug       = $uri[0].'/'.$uri[1].'/page/'.$nextPage.'/';
            }
            return $this->cache->set($key, [
                'title' => $titleTitle.$titlePage.$titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->cache->set($key, false);
    }

    /**
     * Gets an array for the previous page or post returning its title and slug. Works on single, home, author, tag, category requests
     *
     * @param   NULL
     * @return  array|FALSE
     */
    public function the_previous_page()
    {
        # Not found don't bother
        if ($this->is_not_found())
        {
            return false;
        }

        # Get from the cache
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
       
        # There are only next/prev pages for single, tags, category, author, and homepage 
        if (!in_array($this->requestType, ['single', 'home', 'tag', 'category', 'author']) && !$this->is_custom_post())
        {
            return $this->cache->set($key, false);
        }
        
        # Load from cache if we can
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }
        
        # If this is a single or custom post just find the next post
        if ($this->is_single() || $this->is_custom_post())
        {
            return $this->cache->set($key, $this->findPrevPost($this->post));
        }

        # This must now be a paginated page - tag, category, author or homepage listing
        # Get the current page + posts per page and check if there is a page before that
        if ($this->pageIndex > 0 )
        {
            $perPage  = $this->config->get('cms.posts_per_page');
            $page     = $this->pageIndex - 1;
            $offset   = $page * $perPage;
            $limit    = 1;
            $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->queryStr);
            $posts    = $this->queryParser->parseQuery($queryStr);
        }
        else
        {
            $posts = [];
        }
        
        if (!empty($posts))
        {
            $prevpage   = $this->pageIndex;
            $uri        = explode("/", trim($this->request->environment()->REQUEST_URI, '/'));
            $titleBase  = $this->website_title();
            $titlePage  = $prevpage > 1 ? 'Page '.$prevpage.' | ' : '';
            $titleTitle = '';
            if ($this->is_home() )
            {
                $slug = $prevpage > 1 ? 'page/'.$prevpage.'/' : '';
            }
            else if ($this->is_tag() || $this->is_category() || $this->is_author() )
            {
                $titleTitle = $this->the_taxonomy()->name.' | ';
                $slug       = $prevpage > 1 ? $uri[0].'/'.$uri[1].'/page/'.$prevpage.'/' : $uri[0].'/'.$uri[1].'/';
            }
            else if ($this->is_search())
            {
                $titleTitle = 'Search Results | ';
                $slug       =  $prevpage > 1 ? $uri[0].'/'.$uri[1].'/page/'.$prevpage.'/' : $uri[0].'/'.$uri[1].'/';
            }
            return $this->cache->set($key, [
                'title' => $titleTitle.$titlePage.$titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->cache->set($key, false);
    }

    /**
     * Get the title of the next page or post. Works on single, home, author, tag, category requests
     *
     * @param   NULL
     * @return  string|FALSE
     */
    public function the_next_page_title()
    {
        $next_page = $this->the_next_page();
        if ($next_page && isset($next_page['title'])) return $next_page['title'];
        return false;
    }

    /**
     * Get the title of the previous page or post. Works on single, home, author, tag, category requests
     *
     * @param   NULL
     * @return  string|FALSE
     */
    public function the_previous_page_title()
    {
        $prev_page = $this->the_previous_page();
        if ($prev_page && isset($prev_page['title'])) return $prev_page['title'];
        return false;
    }

    /**
     * Get the full URL of the next page or post. Works on single, home, author, tag, category requests
     *
     * @param   NULL
     * @return  string|FALSE
     */
    public function the_next_page_url()
    {
        $next_page = $this->the_next_page();
        if ($next_page && isset($next_page['slug'])) return $this->request->environment()->HTTP_HOST.'/'.$next_page['slug'];
        return false;
    }

    /**
     * Get the full URL of the previous page or post. Works on single, home, author, tag, category requests
     *
     * @param   NULL
     * @return  string|FALSE
     */
    public function the_previous_page_url()
    {
        $prev_page = $this->the_previous_page();
        if ($prev_page && isset($prev_page['slug'])) return $this->request->environment()->HTTP_HOST.'/'.$prev_page['slug'];
        return false;
    }

    /**
     * Returns the searched query for search result requests
     *
     * @param   NULL
     * @return  string|FALSE
     */
    public function search_query()
    {
        if ($this->is_search()) return urldecode($this->searchQuery);
        return false;
    }

    /**
     * Returns the post count of the current page of results for the current request
     *
     * @param   NULL
     * @return  integer
     */
    public function the_posts_count()
    {
        return $this->postCount;
    }

    /**
     * Returns the "KANSO_POSTS_PER_PAGE" value
     *
     * @param   NULL
     * @return  integer
     */
    public function posts_per_page()
    {
        return $this->config->get('cms.posts_per_page');
    }

    /**
     * Do we have posts? or does a post by id exist ?
     *
     * @param   integer  $post_id (optional) (default NULL)
     * @return  bool
     */
    public function have_posts($post_id = null)
    {
        if ($post_id)
        {
            return !empty($this->getPostByID($post_id));
        }

        return $this->postIndex < $this->postCount -1;
    }

    /**
     * Rewind the internal pointer to the '-1'
     *
     * @param   NULL
     * @return  NULL
     */
    public function rewind_posts()
    {
        $this->postIndex = -1;

        if ($this->postCount > 0 )
        {
            $this->post = $this->posts[0];
        }
    }

    /**
     * Iterate to the next post
     *
     * @param   NULL
     * @return  \kanso\articles\Article|NULL
     */
    public function _next()
    {
        $this->postIndex++;
        
        if (isset($this->posts[$this->postIndex]))
        {
            $this->post = $this->posts[$this->postIndex];
        }
        else
        {
            $this->post = null;
        }

        return $this->post;
    }

    /**
     * Iterate to the previous post
     *
     * @param   NULL
     * @return  \kanso\articles\Article|NULL
     */
    public function _previous()
    {
        $this->postIndex--;

        if (isset($this->posts[$this->postIndex]))
        {
            $this->post = $this->posts[$this->postIndex];
        }
        else
        {
            $this->post = NULL;
        }

        return $this->post;
    }

    /**
     * Get all static pages
     *
     * @return array
     */
    public function all_static_pages($published = true) 
    {
        return $this->postManager->provider()->byKey('posts.type', 'page', false, $published);
    }

    /**
     * Get an array of all the tag rows directly from the database.
     *
     * @param   NULL
     * @return  array
     */
    public function all_the_tags()
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        $tags = [];
        $rows = $this->SQL->SELECT('id')->FROM('tags')->FIND_ALL();

        foreach ($rows as $row)
        {
            $tags[] = $this->tagManager->byId($row['id']);
        }

        return $this->cache->set($key, $tags);
    }

    /**
     * Get an array of all the category rows directly from the database.
     *
     * @param   NULL
     * @return  array
     */
    public function all_the_categories()
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        $cats = [];

        $rows = $this->SQL->SELECT('id')->FROM('categories')->FIND_ALL();

        foreach ($rows as $row)
        {
            $cats[] = $this->categoryManager->byId($row['id']);
        }

        return $this->cache->set($key, $cats);
    }

    /**
     * Get an array of all the author rows directly from the database.
     *
     * @param   NULL
     * @return  array
     */
    public function all_the_authors()
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        $authors = [];
        
        $rows = $this->SQL->SELECT('id, role')->FROM('users')->FIND_ALL();

        foreach ($rows as $row)
        {
            if ($row['role'] !== 'administrator' && $row['role'] !== 'writer')
            {
                continue;
            }

            $authors[] = $this->userManager->byId($row['id']);
        }
       
        return $this->cache->set($key,  $authors);
    }

    /**
     * Display the contents of header.php
     *
     * @param   NULL
     * @return  string
     */
    public function the_header()
    {
        return $this->response->view()->display($this->theme_directory().DIRECTORY_SEPARATOR.'header.php');
    }

    /**
     * Display the contents of footer.php
     *
     * @param   NULL
     * @return  string
     */
    public function the_footer()
    {
        return $this->response->view()->display($this->theme_directory().DIRECTORY_SEPARATOR.'footer.php');
    }

    /**
     * Display the contents of sidebar.php
     *
     * @param   NULL
     * @return  string
     */
    public function the_sidebar()
    {
        return $this->response->view()->display($this->theme_directory().DIRECTORY_SEPARATOR.'sidebar.php');
    }

    /**
     * Display the contents of any template file relative to the theme's base directory
     *
     * @param   NULL
     * @return  string
     */
    public function include_template($template_name, $data = [])
    {
        $template = $this->theme_directory().DIRECTORY_SEPARATOR.$template_name.'.php';
        
        if (file_exists($template))
        {
            return $this->response->view()->display($template, $data);
        }
        return '';
    }

    /**
     * Get the path to the theme directory that holds all the themes
     *
     * @access public
     * @return string
     */
    public function themes_directory(): string
    {
        return $this->config->get('cms.themes_path');
    }

    /**
     * Get the path to the theme directory that holds all the theme folders
     *
     * @access public
     * @return string
     */
    public function theme_name(): string
    {
        return $this->config->get('cms.theme_name');
    }

    /**
     * Get the path to the theme directory that holds the currently active theme.
     *
     * @param   NULL
     * @return  string
     */
    public function theme_directory() 
    {
        return $this->themes_directory().'/'.$this->theme_name();
    }


    /**
     * Get the URL to the theme directory that holds the currently active theme.
     *
     * @param   NULL
     * @return  string
     */
    public function theme_url() 
    {
        return str_replace($this->request->environment()->DOCUMENT_ROOT, $this->request->environment()->HTTP_HOST, $this->theme_directory());
    }

    /**
     * Get the homepage URL
     *
     * @param   NULL
     * @return  string
     */
    public function home_url() 
    {
        return $this->request->environment()->HTTP_HOST;
    }

    /**
     * Get the website's domain name (e.g "example.com")
     *
     * @param   NULL
     * @return  string
     */
    public function domain_name() 
    {
        return $this->request->environment()->DOMAIN_NAME;
    }

    /**
     * Get the website title from the config
     *
     * @param   NULL
     * @return  string
     */
    public function website_title() 
    {
        return $this->config->get('cms.site_title');
    }

    /**
     * Get the website description from the config
     *
     * @param   NULL
     * @return  string
     */
    public function website_description() 
    {
        return $this->config->get('cms.site_description');
    }

    /**
     * Get the meta description to display in the website's head
     *
     * @param   NULL
     * @return  string
     */
    public function the_meta_description()
    {
        if ($this->is_not_found())
        {
            return 'The page you are looking for could not be found.';
        }
        
        $description = $this->website_description();
        
        if ($this->is_single() || $this->is_page() || $this->is_custom_post())
        {
            $description = $this->post->excerpt;
        }
        else if ($this->is_search())
        {
            $description = 'Search Results for: '.$this->search_query().' - '.$this->website_title();
        }

        return Str::reduce($description, 180);
    }

    /**
     * Get the meta title to display in the website's head
     *
     * @param   NULL
     * @return  string
     */
    public function the_meta_title()
    {
        $uri        = explode("/", trim($this->request->environment()->REQUEST_URI, '/'));
        $titleBase  = $this->website_title();
        $titlePage  = $this->pageIndex > 0 ? 'Page '.($this->pageIndex+1).' | ' : '';
        $titleTitle = '';

        if ($this->is_not_found()) return 'Page Not Found';

        if ($this->is_single() || $this->is_page() || $this->is_custom_post())
        {
            if ($this->have_posts())
            {
                $titleTitle = $this->post->title.' | ';
            }
        }
        else if ($this->is_tag() || $this->is_category() || $this->is_author())
        {
            $titleTitle = $this->the_taxonomy()->name.' | ';
        }
        else if ($this->is_search())
        {
            $titleTitle = 'Search Results | ';
        }

        return  $titleTitle.$titlePage.$titleBase;
    }

    /**
     * Get the canonical URL
     *
     * @param   NULL
     * @return  string
     */
    public function the_canonical_url()
    {
       
        $page = $this->pageIndex;
        $env  = $this->request->environment()->asArray();
        $base = $env['HTTP_HOST'];
        $uri  = explode("/", trim($env['REQUEST_URI'], '/'));
        $slug = '';
        if (!$this->have_posts() || $this->is_not_found())
        {
            return $env['HTTP_HOST'].$env['REQUEST_URI'];
        }

        if ($this->is_single() || $this->is_page() || $this->is_custom_post())
        {
            $slug = $this->post->slug;
        }
        if ($this->is_home() )
        {
            $slug = $page > 1 ? 'page/'.$page.'/' : '';
        }
        else if ($this->is_tag() || $this->is_category() || $this->is_author() )
        {
            $slug = $page > 1 ? $uri[0].'/'.$uri[1].'/page/'.$page.'/' : $uri[0].'/'.$uri[1].'/';
        }
        else if ($this->is_search())
        {
            $slug = $page > 1 ? $uri[0].'/'.$uri[1].'/page/'.$page.'/' : $uri[0].'/'.$uri[1].'/';
        }
        else
        {
            return $env['HTTP_HOST'].$env['REQUEST_URI'];
        }
        return "$base/$slug";
    }

    /**
     * Get the currently logged in Kanso user (if any)
     *
     * @param   NULL
     * @return \kanso\auth\adapters\User|FALSE
     */
    public function user() 
    {
        return $this->gatekeeper->getUser();
    }

    /**
     * Is the current user (if any) logged in
     *
     * @param   NULL
     * @return  boolean
     */
    public function is_loggedIn() 
    {
        return $this->gatekeeper->isLoggedIn();
    }

    /**
     * Is the current user (if any) allowed to access the admin panel
     *
     * @param   NULL
     * @return boolean
     */
    public function user_is_admin() 
    {
        return $this->gatekeeper->isAdmin();
    }

    /**
     * Are comments (if enabled globally) enabled on the current post or a post by id
     *
     * @param  integer    $post_id    (optional) (default NULL)
     * @return boolean
     */
    public function comments_open($post_id = null) 
    {
        if ($this->config->get('cms.enable_comments') === false)
        {
            return false;
        }
        
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);

            if ($post)
            {
                return $post->comments_enabled == true;
            }

            return false;
        }

        if (!empty($this->post))
        {
            return $this->post->comments_enabled == true;
        }

        return false;
    }

    /**
     * Does the current post or a post by id have any comments ?
     *
     * @param  integer    $post_id    (optional) (default NULL)
     * @return boolean
     */
    public function has_comments(int $post_id = null) 
    {        
        return !empty($this->get_comments($post_id));
    }

    /**
     * How many approved comments does the current post or a post by id have
     *
     * @param  integer    $post_id    (optional) (default NULL)
     * @return integer
     */
    public function comments_number($post_id = null)
    {
        return count($this->get_comments($post_id));
    }

    /**
     * Get a single comment row from the databse by id
     *
     * @param  integer    $comment_id    
     * @return array
     */
    public function get_comment($comment_id)
    {
        return $this->commentManager->byId($comment_id);
    }

    /**
     * Get all of the current post or a post by id's comments
     *
     * @param  integer    $post_id       (optional) (default NULL)
     * @param  integer    $approvedOnly  (optional) (default TRUE)
     * @return array
     */
    public function get_comments($post_id = null)
    {
        if ($post_id)
        {
            $post = $this->getPostByID($post_id);

            if ($post)
            {
                return $post->comments;
            }

            return [];
        }

        if (!empty($this->post))
        {
            return $this->post->comments;
        }

        return [];
    }

    /**
     * Get the HTML that displays the comments of the current post or a post by id
     *
     * @param  array      $args       (optional) (default NULL)
     * @param  integer    $post_id    (optional) (default NULL)
     * @return string
     */
    public function display_comments($args = null, $post_id = null)
    {
        # If there no comments return empty string
        if ($this->comments_number($post_id) === 0)
        {
            return '';
        }

        # HTML string
        $HTML = '';

        # Save the article row locally
        $post  = !$post_id ? $this->post : $this->getPostByID($post_id);

        # Fallback incase nothing is present
        if (!$post || empty($post)) return '';

        # Save the article permalink locally
        $permalink = $this->the_permalink($post->id);

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
        $formTemplate = $this->theme_directory().DIRECTORY_SEPARATOR.'comments.php';
        
        if (file_exists($formTemplate))
        {
            return $this->include_template('comments', ['comments' => $comments] );
        }

        # Start looping comments
        $HTML = $this->commentToString($comments, $options, $permalink, false); 

        return $HTML;
    }

    /**
     * Get the HTML that displays the comment form of the current post or a post by id
     *
     * @param  array      $args          (optional) (default NULL)
     * @param  integer    $post_id       (optional) (default NULL)
     * @return string
     */
    public function comment_form($args = null, $post_id = null)
    {
        # Load from template if it exists
        $formTemplate = $this->theme_directory().DIRECTORY_SEPARATOR.'commentform.php';
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
                <input id="comment-postId" type="hidden" name="post-id" style="display:none" value="(:postID)" />
            ',

            'reply_id' => '',

            'reply_id_field' => '
                <input id="comment-replyId" type="hidden" name="reply-id" style="display:none" value="(:replyID)" />
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
        if (!$this->comments_open($post_id))
        {
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

        $domain = $this->request->isSecure() ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';

        # If there is an error with the emaill or md5 default to fallback 
        # force a mystery man
        if (!$isMd5 && !$isEmail)
        {
            if ($srcOnly) return $domain.'/avatar/0?s='.$size.'&d=mm';
            return '<img src="'.$domain.'/avatar/0?s='.$size.'&d=mm"/>';
        }
        
        if ($isEmail) $md5 = md5( strtolower( trim( $email_or_md5 ) ) );
        if ($isMd5)   $md5 = $email_or_md5;
       
        if ($srcOnly) return $domain.'/avatar/'.$md5.'?s='.$size.'&d=mm';
        return '<img src="'.$domain.'/avatar/'.$md5.'?s='.$size.'&d=mm"/>';
    
    }

    /**
     * Build the pagination links for the current page. Works on home, search, tag, category, author requests
     *
     * @param  array      $args          (optional) (default NULL)
     * @return string
     */
    public function pagination_links($args = null) 
    {
        # Default options
        $options = [
          'base'               => $this->request->environment()->HTTP_HOST,
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
        $uri = explode("/", trim($this->request->environment()->REQUEST_URI, '/'));

        # Declare the pagination string
        $pagination = '';

        # Count the posts
        $posts = $this->queryParser->countQuery();
        $pages = Arr::paginate($posts, $this->pageIndex, $this->config->get('cms.posts_per_page'));

        # If no args were defined, Kanso will figure it out for us
        if (!$args || !isset($args['current']) || !isset($args['total']))
        {
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
        if ($this->is_search())
        {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.'search-results/?q='.$this->searchQuery;
        }
        else if ($this->is_tag())
        {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[0].DIRECTORY_SEPARATOR.$uri[1];
        }
        else if ($this->is_category())
        {
            $options['base'] = $options['base'].DIRECTORY_SEPARATOR.$uri[0].DIRECTORY_SEPARATOR.$uri[1];
        }
        else if ($this->is_author())
        {
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
        if ($options['show_all'] === true)
        {
            $frontEllipsis = '';
            $backEllipsis  = '';
            $loopStart     = 2;
            $loopEnd       = $options['total'];
        }
        
        # If show previous
        if ($options['prev_next'] === true)
        {
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
        for ($i = $loopStart; $i < $loopEnd; $i++)
        {
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
        if ($options['prev_next'] === true)
        {
            $class  = $options['current'] <  $options['total'] ? '' : 'disabled' ;
            $format = $options['current'] <  $options['total'] ? $options['format'] : $options['format_disabled'] ;
            $link   = $options['current'] <  $options['total'] ? $options['base'].DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.($options['current']+1).DIRECTORY_SEPARATOR : '#';
            $replacements = [$class, $link, $options['next_text']];
            $pagination  .= preg_replace($patterns, $replacements, $format).$options['white_space'];
        }

        return $pagination;

    }

    /**
     * Return the HTML for the search form
     *
     * @param  NULL
     * @return string      user's avatar or default mystery on fallback
     */
    public function get_search_form() 
    {
        # Load from template if it exists
        $formTemplate = $this->theme_directory().DIRECTORY_SEPARATOR.'searchform.php';
        if (file_exists($formTemplate)) return $this->include_template('searchform');
        
        return '

            <form role="search" method="get" action="'.$this->home_url().'/search-results/">

                <fieldset>
                        
                        <label for="search_input">Search: </label>
                        
                        <input type="search" name="q" id="search_input" placeholder="Search...">

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
        $HTML = '';

        foreach ($comments as $comment)
        {
            $patterns     = [];
            $replacements = [];

            $commentStr = $options['format'];

            # Replace classnames
            foreach ($options['classes'] as $suffix => $classname)
            {
                $patterns[]     = '/\(:classes_'.$suffix.'\)/';
                $class          = 'class="'.$classname;
                if ($suffix === 'wrap' && $isChild)
                {
                    $class .= ' '.$options['classes']['child_wrap'];
                }

                if ($suffix === 'children_wrap' && empty($comment->children()))
                {
                    $class .= ' '.$options['classes']['no_children'];
                }

                $replacements[] = $class.'"';
            }

            # Replace ID
            $patterns[]     = '/\(:id\)/';
            $replacements[] = $comment->id;

            # Replace avatar src
            $patterns[]     = '/\(:avatar_src\)/';
            $replacements[] = $this->get_gravatar($comment->email, $options['avatar_size'], true);

            # Replace avatar size
            $patterns[]     = '/\(:avatar_size\)/';
            $replacements[] =  $options['avatar_size'];

            # Replace comment author name
            $patterns[]     = '/\(:comment_name\)/';
            $replacements[] = $comment->name;

            # Replace Link text
            $patterns[]     = '/\(:link_text\)/';
            $replacements[] = $options['link_text'];

            # Replace time text
            $patterns[]     = '/\(:comment_time_GMT\)/';
            $replacements[] = date("c", $comment->date);

            $patterns[]     = '/\(:comment_time_format\)/';
            $replacements[] = date($options['time_format'], $comment->date);

            # Replace content
            $patterns[]     = '/\(:comment_content\)/';
            $replacements[] = $comment->html_content;

            # Replace permalinks
            $patterns[]     = '/\(:permalink\)/';
            $replacements[] = $permalink;
            
            $commentStr = preg_replace($patterns, $replacements, $commentStr);

            if (!empty($comment->children()))
            {
                
                $commentStr = preg_replace( '/\(:children\)/', $this->commentToString($comment->children(), $options, $permalink, true), $commentStr);
            }
            else
            {
                $commentStr = preg_replace( '/\(:children\)/', '', $commentStr);
            }
            
            $HTML .= $commentStr;
        }
       
       return $HTML;

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
        if (!empty($next))
        {
            $next = array_values($next);
            foreach ($next as $i => $prevPost)
            {
                if ((int)$prevPost['id'] === (int)$post->id)
                {
                    if (isset($next[$i+1]))
                    {
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
        if (!empty($next))
        {
            $next = array_values($next);
            foreach ($next as $i => $prevPost)
            {
                if ((int)$prevPost['id'] === (int)$post->id)
                {
                    if (isset($next[$i+1]))
                    {
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
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->postManager->byId($post_id));
    }

    private function getAuthorById($author_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->userManager->provider()->byId($author_id));
    }

    private function getTagById($tag_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->tagManager->provider()->byId($tag_id));
    }

    private function getCategoryById($category_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->categoryManager->provider()->byId($category_id));
    }

    private function getMediaById($thumb_id)
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
        
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->mediaManager->provider()->byId($thumb_id));
    }
}
