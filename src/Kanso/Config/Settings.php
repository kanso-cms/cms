<?php

namespace Kanso\Config;

/**
 * Settings
 * 
 */
class Settings 
{
    /**
     * @var int
     */
    const INVALID_THEME = 100;

    /**
     * @var int
     */
    const INVALID_PERMALINKS = 200;

    /**
     * @var int
     */
    const INVALID_IMG_QUALITY = 300;

    /**
     * @var int
     */
    const INVALID_CDN_URL = 400;

    /**
     * @var int
     */
    const INVALID_CACHE_LIFE = 500;

    /**
     * @var int
     */
    const INVALID_THUMBNAILS = 600;

      /**
     * @var int
     */
    const INVALID_POSTS_PER_PAGE = 700;

    /**
     * @var int
     */
    const UNKNOWN_ERROR = 2000;

    /**
     * @var array    Default Kanso configuration options
     */
    public $defaults = [

        # Database connection settings
        'host'         => 'localhost',
        'user'         => 'root',
        'password'     => 'root',
        'dbname'       => 'Kanso',
        'table_prefix' => 'kanso_',

        # Core Kanso configuration
        'KANSO_THEME_NAME'       => 'Roshi',
        'KANSO_SITE_TITLE'       => 'Kanso',
        'KANSO_SITE_DESCRIPTION' => 'Kanso is a lightweight CMS written in PHP with a focus on simplicity, usability and of course writing.',
        'KANSO_SITEMAP'          => 'sitemap.xml',
        'KANSO_PERMALINKS'       => 'year/month/postname/',
        'KANSO_PERMALINKS_ROUTE' => '(:year)/(:month)/(:postname)/',
        'KANSO_POSTS_PER_PAGE'   => 10,
        'KANSO_ROUTE_TAGS'       => true,
        'KANSO_ROUTE_CATEGORIES' => true,
        'KANSO_ROUTE_AUTHORS'    => true,
        'KANSO_THUMBNAILS'       => [400, 800, 1200],
        'KANSO_IMG_QUALITY'      => 80,
        'KANSO_USE_CDN'          => false,
        'KASNO_CDN_URL'          => '',
        'KANSO_USE_CACHE'        => false,
        'KANSO_CACHE_LIFE'       => '',
        'KANSO_COMMENTS_OPEN'    => true,
        'KANSO_STATIC_PAGES'     => [],
        'KANSO_AUTHOR_SLUGS'     => ['john-appleseed'],

        # Author login infomation
        'KANSO_OWNER_USERNAME'  => 'admin',
        'KANSO_OWNER_EMAIL'     => 'admin@example.com',
        'KANSO_OWNER_PASSWORD'  => 'password1',
    ];

    /**
     * @var array    Current config
     */
    private $data = [];

    /**
     * @var array    Current config
     */
    private $prevData = [];

    /**
     * @var string    Path to config file
     */
    private $path;

    /**
     * @var string    \Kanso\Config\Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param boolean    $freshInstall    Load and save settings from Install.php instead of Config.php
     *
     */
    public function __construct($freshInstall = false)
    {
        if ($freshInstall) {
            $this->path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Install.php';
        }
        else {
            $this->path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Config.php';
        }
        
        $this->validator = new \Kanso\Config\Validator($this->defaults);
        
        $this->parse();
    }

    /**
     * Parse the data from file and load internally
     *
     */
    public function parse()
    {
        if (file_exists($this->path) && is_file($this->path)) {
            $this->data     = include($this->path);
            $this->prevData = $this->data;
        }
        return $this->data;
    }

    /********************************************************************************
    * MAGIC METHOD OVERRIDES
    *******************************************************************************/
    public function __get($key)
    {
        if (array_key_exists($key, $this->data)) return $this->data[$key];
        return NULL;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __isset($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function __unset($key)
    {
        if (array_key_exists($key, $this->data)) unset($this->data[$key]);
    }

    /********************************************************************************
    * PUBLIC METHODS
    *******************************************************************************/

    /**
     * Save current settings to file
     *
     * @param boolean   $validate    Validate before saving
     */
    public function save($validate = false)
    {
       return $this->dosave($validate);
    }
    
    /**
     * Refresh the settings
     *
     */
    public function refresh()
    {
        $this->parse();
    }

    /**
     * Get all the settings
     *
     */
    public function data()
    {
        return $this->data;
    }


    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Save handler
     *
     * @param boolean   $validate    Validate before saving
     */
    private function dosave($validate)
    {
        # Validate the config if needed
        if ($validate) {
            $isValid = $this->validator->validate($this->data);
            if ($isValid !== true) return $isValid;
        }

        $this->data = $this->validator->filter($this->data);

        # Encode and save the config
        file_put_contents($this->path, "<?php\nreturn\n".var_export($this->data, true).";?>");

        # Check if the user has changed the permalinks structure
        $changedPermalinks = $this->prevData['KANSO_PERMALINKS_ROUTE'] !== $this->data['KANSO_PERMALINKS_ROUTE'];

        # Check if the user has changed use cache
        $changedCache = $this->prevData['KANSO_USE_CACHE'] !== $this->data['KANSO_USE_CACHE'];

        # Check if the CDN has change - so the cache needs to be update
        $changedCache = $this->prevData['KANSO_USE_CDN'] !== $this->data['KANSO_USE_CDN'] && $this->data['KANSO_USE_CACHE'] === true ? true : $changedCache;

        # If permalinks were changed, we need to update every post in the DB
        if ($changedPermalinks) $this->updatePostPermalinks();

        # Clear the cache as well
        if ($changedCache) \Kanso\Kanso::getInstance()->Cache->clear();

        # Data
        $this->prevData = $this->data;

        return true;
    }

    /**
     * Update all the permalinks (slugs) in the database with
     * Kanso's configuration
     */
    private function updatePostPermalinks() 
    {
        # Get the Kanso SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        # Get all the articles 
        $allPosts = $this->getAllArticles();
       
        # Loop through the articles and update the slug and permalink
        if ($allPosts && !empty($allPosts)) {
            foreach ($allPosts as $post) {
                $newSlug = $this->titleToSlug($post['title'], $post['category']['slug'], $post['author']['slug'], $post['created'], $post['type']);
                $SQL->UPDATE('posts')->SET(['slug' => $newSlug])->WHERE('id', '=', $post['id'])->QUERY();
            }
        }
    }

    private function getAllArticles()
    {
        # Get the Kanso Query builder
        $Query = \Kanso\Kanso::getInstance()->Query;

        # Get the Kanso SQL builder
        $SQL = \Kanso\Kanso::getInstance()->Database->Builder();

        # Select the posts
        $SQL->SELECT('posts.*')->FROM('posts');

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

        return $articles;
    }

    /**
     * Convert a title to a slug with permalink structure
     *
     * @param  string    $title             The title of the article
     * @param  string    $categorySlug      The category slug
     * @param  string    $authorSlug        The author's slug
     * @param  int       $created           A unix timestamp of when the article was created
     * @param  string    $type              post/page
     * @return string                       The slug to the article             
     */
    private function titleToSlug($title, $categorySlug, $authorSlug, $created, $type) 
    {

        if ($type === 'page') return \Kanso\Utility\Str::slugFilter($title).'/';
        $format = $this->data['KANSO_PERMALINKS'];
        $dateMap = [
            'year'     => 'Y',
            'month'    => 'm',
            'day'      => 'd',
            'hour'     => 'h',
            'minute'   => 'i',
            'second'   => 's',
        ];
        $varMap  = [
            'postname' => \Kanso\Utility\Str::slugFilter($title),
            'category' => $categorySlug,
            'author'   => $authorSlug,
        ];
        $slug = '';
        $urlPieces = explode('/', $format);
        foreach ($urlPieces as $key) {
            if (isset($dateMap[$key])) $slug .= date($dateMap[$key], $created).'/';
            else if (isset($varMap[$key])) $slug .= $varMap[$key].'/';
        }
        return $slug;

    }
   


}