<?php

namespace Kanso\Config;

/**
 * Events
 *
 * This class is used by Kanso to fire events when they happen throughout
 * the application. The idea here is to make customization easier
 * for people and the ability to create 'puligins'.
 *
 * Note that this class is a singleton.
 * 
 */
class Settings 
{

    /**
     * @var array    Response status
     */
    private $responseCodes = [
        'invalid_theme'       => 100,
        'invalid_permalinks'  => 200,
        'invalid_img_quality' => 300,
        'invalid_cdn_url'     => 400,
        'invalid_cache_life'  => 500,
        'unknown_error'       => 600,
        'success'             => 700,
    ];

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
    private $configData = [];

    /**
     * @var array    Temporary config for updates
     */
    private $tempConfig = [];

    /**
     * @var string    Path to config file
     */
    private $configPath;

    /**
     * Private constructor
     */
    public function __construct($freshInstall = false)
    {
        if ($freshInstall) {
            $this->configPath = realpath(__DIR__ . '/..').DIRECTORY_SEPARATOR.'Install.php';
        }
        else {
            $this->configPath = realpath(__DIR__ . '/..').DIRECTORY_SEPARATOR.'Config.php';
        }
       
        $this->parse();
    }

    /********************************************************************************
    * PUBLIC METHODS
    *******************************************************************************/

    /**
     * Parse Kanso's Config.php file and return the config
     *
     * @return array
     */
    public function parse()
    {
        if (file_exists($this->configPath)) $config = include($this->configPath);
        if ($config) {
            $this->tempConfig = $config;
            $this->configData = $config;
            return $config;
        }
        $this->tempConfig = $this->defaults;
        $this->configData = $this->defaults;
        return $this->defaults;
    }

    /**
     * Set a config key-value pair
     *
     * @param string    $key
     * @param mixed     $value
     * @param boolean   $throwError (optional)
     *
     * @return boolean|integer
     */
    public function put($key, $value, $throwError = false)
    {
       $this->tempConfig[$key] = $value;
       return $this->save($throwError);
    }

    /**
     * Set multiple key-value pairs
     *
     * @param array     $data
     * @param boolean   $throwError (optional)
     *
     * @return boolean|integer
     */
    public function putMultiple($data, $throwError = false)
    {
        foreach ($data as $key => $value) {
            $this->tempConfig[$key] = $value;
        }
        return $this->save($throwError);
    }

    /**
     * Get the whole config or just a single key
     *
     * @param string    $key (optional)
     *
     * @return boolean|mixed
     */
    public function get($key = null)
    {
        if (!$key) return $this->configData;
        if ($this->has($key)) return $this->configData[$key];
        return false;
    }

    /**
     * Remove a key-value pair
     *
     * @param string    $key
     * @param boolean   $throwError (optional)
     *
     * @return boolean|mixed
     */
    public function remove($key, $throwError = false)
    {
       if ($this->has($key)) unset($this->tempConfig[$key]);
       return $this->save($throwError);
    }

    /**
     * Check if a key-value pair exists
     *
     * @param string    $key
     *
     * @return boolean
     */
    public function has($key)
    {
       return array_key_exists($key, $this->configData);
    }

    /**
     * Refresh Kanso's Config - This will also update Kanso's 
     * current config.
     * 
     * @return boolean
     */
    public function refresh()
    {
        $this->parse();
        return $this->save();
    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Save the current config to disk and update Kanso's config.
     *
     * @param  boolean    $throwError   Should the function return an error code or filter the config
     * 
     * @return boolean|integer
     */
    private function save($throwError = false)
    {
        # Fire the event
        \Kanso\Events::fire('configChange', $this->tempConfig);

        # Validate the config if needed
        if ($throwError) {
            $validation = $this->validateConfig();
            if (is_integer($validation) && in_array($validation, $this->responseCodes)) {
                $this->tempConfig = $this->configData;
                return $validation;
            }
        }

        # Filter the config internally
        $config = $this->filterConfig();

        # Filter the config
        $config = \Kanso\Filters::apply('configChange', $config);

        # Encode and save the config
        file_put_contents($this->configPath, "<?php\nreturn\n".var_export($config, true).";?>");

        # Check if the user has changed the permalinks structure
        $changedPermalinks = $config['KANSO_PERMALINKS_ROUTE'] !== $this->configData['KANSO_PERMALINKS_ROUTE'];

        # Check if the user has changed use cache
        $changedCache = $config['KANSO_USE_CACHE'] !== $this->configData['KANSO_USE_CACHE'];

        # Check if the CDN has change - so the cache needs to be update
        $changedCache = $config['KANSO_USE_CDN'] !== $this->configData['KANSO_USE_CDN'] && $config['KANSO_USE_CACHE'] === true ? true : $changedCache;

        # Set the local config
        $this->configData = $config;
        $this->tempConfig = $config;

        # If permalinks were changed, we need to update every post in the DB
        if ($changedPermalinks) $this->updatePostPermalinks();

        # Clear the cache as well
        if ($changedCache) \Kanso\Kanso::getInstance()->Cache->clearCache();

        # Update Kanso
        \Kanso\Kanso::getInstance()->Config = $config;

        if ($throwError) return $this->responseCodes['success'];

        return true;
    }

    /**
     * Validate the pending config changes
     *
     * 
     * @return boolean|integer
     */
    private function validateConfig()
    {
        # Get the current config
        $existingConfig = $this->configData;

        # Get the new config
        $newConfig = $this->tempConfig;

        # Get the Environment
        $env = \Kanso\Kanso::getInstance()->Environment;

        # Declare variables we are going to use and do some santization
        $KansoPermalinks   = $this->createPermalinks($newConfig['KANSO_PERMALINKS']);
        $KansoThumbnails   = $this->createThumbnailsArray($newConfig['KANSO_THUMBNAILS']);
        $KansoCacheLife    = $this->validateCacheLife($newConfig['KANSO_CACHE_LIFE']);

        # Build the new configuration array
        $Kanso_Config = [
            "KANSO_THEME_NAME"       => str_replace("/", '', $newConfig['KANSO_THEME_NAME']),
            "KANSO_SITE_TITLE"       => $newConfig['KANSO_SITE_TITLE'],
            "KANSO_SITE_DESCRIPTION" => $newConfig['KANSO_SITE_DESCRIPTION'], 
            "KANSO_SITEMAP"          => $newConfig['KANSO_SITEMAP'],
            "KANSO_PERMALINKS"       => $KansoPermalinks['KANSO_PERMALINKS'],
            "KANSO_PERMALINKS_ROUTE" => $KansoPermalinks['KANSO_PERMALINKS_ROUTE'],
            "KANSO_AUTHOR_SLUGS"     => $existingConfig['KANSO_AUTHOR_SLUGS'],
            "KANSO_POSTS_PER_PAGE"   => (int)$newConfig['KANSO_POSTS_PER_PAGE'] < 1 ? 10 : $newConfig['KANSO_POSTS_PER_PAGE'],
            "KANSO_ROUTE_TAGS"       => $newConfig['KANSO_ROUTE_TAGS'],
            "KANSO_ROUTE_CATEGORIES" => $newConfig['KANSO_ROUTE_CATEGORIES'],
            "KANSO_ROUTE_AUTHORS"    => $newConfig['KANSO_ROUTE_AUTHORS'],
            "KANSO_THUMBNAILS"       => $KansoThumbnails,
            "KANSO_IMG_QUALITY"      => (int)$newConfig['KANSO_IMG_QUALITY'],
            "KANSO_USE_CDN"          => $newConfig['KANSO_USE_CDN'],
            "KASNO_CDN_URL"          => $newConfig['KASNO_CDN_URL'],
            "KANSO_USE_CACHE"        => $newConfig['KANSO_USE_CACHE'],
            "KANSO_CACHE_LIFE"       => $KansoCacheLife,
            "KANSO_COMMENTS_OPEN"    => $newConfig['KANSO_COMMENTS_OPEN'],
            "KANSO_STATIC_PAGES"     => $existingConfig['KANSO_STATIC_PAGES'],
        ];

        # Validate things that are required
        if ($Kanso_Config['KANSO_THEME_NAME'] === '' || !is_dir($env['KANSO_THEME_DIR'].'/'.$Kanso_Config['KANSO_THEME_NAME'])) return $this->responseCodes['invalid_theme'];
        if ($KansoPermalinks['KANSO_PERMALINKS'] === '' || $KansoPermalinks['KANSO_PERMALINKS_ROUTE'] === '' || strpos($KansoPermalinks['KANSO_PERMALINKS'], 'postname') === false) return $this->responseCodes['invalid_permalinks'];
        if ($Kanso_Config['KANSO_IMG_QUALITY'] < 1 || $Kanso_Config['KANSO_IMG_QUALITY'] > 100)return $this->responseCodes['invalid_img_quality'];
        if ($Kanso_Config['KANSO_USE_CDN'] === true && $Kanso_Config['KASNO_CDN_URL'] === '') return $this->responseCodes['invalid_cdn_url'];
        if ($Kanso_Config['KANSO_USE_CACHE'] === true && !$KansoCacheLife) return $this->responseCodes['invalid_cache_life'];

        $this->tempConfig = array_merge($this->tempConfig, $Kanso_Config);

        return true;
    }

    /**
     * Filter and sanitize the configuration to unsure Kanso will run
     * 
     * @return array
     */
    private function filterConfig($throwError = false)
    {
        # Merge the config with the defaults
        $config = array_merge($this->defaults, $this->tempConfig);

        # Filter and sanitize the config
        $config['host']                     = filter_var($config['host'], FILTER_SANITIZE_STRING);
        $config['user']                     = filter_var($config['user'], FILTER_SANITIZE_STRING);
        $config['password']                 = filter_var($config['password'], FILTER_SANITIZE_STRING);
        $config['dbname']                   = filter_var($config['dbname'], FILTER_SANITIZE_STRING);
        $config['table_prefix']             = filter_var($config['table_prefix'], FILTER_SANITIZE_STRING);
        $config['KANSO_THEME_NAME']         = filter_var($config['KANSO_THEME_NAME'], FILTER_SANITIZE_STRING);
        $config['KANSO_SITE_TITLE']         = filter_var($config['KANSO_SITE_TITLE'], FILTER_SANITIZE_STRING);
        $config['KANSO_SITE_DESCRIPTION']   = filter_var($config['KANSO_SITE_DESCRIPTION'], FILTER_SANITIZE_STRING);
        $config['KANSO_SITEMAP']            = filter_var($config['KANSO_SITEMAP'], FILTER_SANITIZE_STRING);
        $config['KANSO_PERMALINKS']         = filter_var($config['KANSO_PERMALINKS'], FILTER_SANITIZE_STRING);
        $config['KANSO_PERMALINKS_ROUTE']   = filter_var($config['KANSO_PERMALINKS_ROUTE'], FILTER_SANITIZE_STRING);
        $config['KANSO_POSTS_PER_PAGE']     = (int) $config['KANSO_POSTS_PER_PAGE'];
        $config['KANSO_ROUTE_TAGS']         = \Kanso\Utility\Str::bool( $config['KANSO_ROUTE_TAGS']);
        $config['KANSO_ROUTE_CATEGORIES']   = \Kanso\Utility\Str::bool($config['KANSO_ROUTE_CATEGORIES']);
        $config['KANSO_ROUTE_AUTHORS']      = \Kanso\Utility\Str::bool($config['KANSO_ROUTE_AUTHORS']);
        $config['KANSO_THUMBNAILS']         = $config['KANSO_THUMBNAILS'];
        $config['KANSO_IMG_QUALITY']        = (int) $config['KANSO_IMG_QUALITY'];
        $config['KANSO_USE_CDN']            = \Kanso\Utility\Str::bool($config['KANSO_USE_CDN']);
        $config['KASNO_CDN_URL']            = filter_var($config['KASNO_CDN_URL'], FILTER_SANITIZE_STRING);
        $config['KANSO_USE_CACHE']          = \Kanso\Utility\Str::bool( $config['KANSO_USE_CACHE']);
        $config['KANSO_CACHE_LIFE']         = filter_var($config['KANSO_CACHE_LIFE'], FILTER_SANITIZE_STRING);
        $config['KANSO_COMMENTS_OPEN']      = \Kanso\Utility\Str::bool($config['KANSO_COMMENTS_OPEN']);
        $config['KANSO_OWNER_USERNAME']     = filter_var($config['KANSO_OWNER_USERNAME'], FILTER_SANITIZE_STRING);
        $config['KANSO_OWNER_EMAIL']        = filter_var($config['KANSO_OWNER_EMAIL'], FILTER_SANITIZE_STRING);
        $config['KANSO_OWNER_PASSWORD']     = filter_var($config['KANSO_OWNER_PASSWORD'], FILTER_SANITIZE_STRING);
        $config['KANSO_STATIC_PAGES']       = $config['KANSO_STATIC_PAGES'];
        $config['KANSO_AUTHOR_SLUGS']       = $config['KANSO_AUTHOR_SLUGS'];
        
        # Filter the sanitize the sitemap
        if (strpos($config['KANSO_SITEMAP'], '.') === false) $config['KANSO_SITEMAP'] = $this->defaults['KANSO_SITEMAP'];

        # Fiter and sanitize the permalinks
        $permalinks = $this->createPermalinks($config['KANSO_PERMALINKS']);
        if (empty($permalinks['KANSO_PERMALINKS']) || empty($permalinks['KANSO_PERMALINKS_ROUTE'])) {
            $config['KANSO_PERMALINKS_ROUTE'] = $this->defaults['KANSO_PERMALINKS_ROUTE'];
            $config['KANSO_PERMALINKS']       = $this->defaults['KANSO_PERMALINKS'];
        }

        # Fiter and sanitize the posts per page
        if ($config['KANSO_POSTS_PER_PAGE'] <= 0) $config['KANSO_POSTS_PER_PAGE'] = $this->defaults['KANSO_POSTS_PER_PAGE'];

        # Fiter and sanitize the thumbnail sizes
        if (!is_array($config['KANSO_THUMBNAILS'])) {
            $config['KANSO_THUMBNAILS'] = array_map('trim', explode(',', (string)$config['KANSO_THUMBNAILS']));
        }

        foreach ($config['KANSO_THUMBNAILS'] as $i => $thumbs) {
            if (is_integer($thumbs) || is_array($thumbs)) continue;
            $thumbs = array_map('trim', explode(' ', $thumbs));
            if (count($thumbs) === 2) {
                $config['KANSO_THUMBNAILS'][$i] = [intval($thumbs[0]), intval($thumbs[1])];
            }
            else {
                $config['KANSO_THUMBNAILS'][$i] = intval($thumbs[0]);
            }
        }

        # Fiter and sanitize the image quality 
        if ($config['KANSO_IMG_QUALITY'] <= 0 || $config['KANSO_IMG_QUALITY'] > 100)  $config['KANSO_IMG_QUALITY'] = $this->defaults['KANSO_IMG_QUALITY']; 

        # Filter and sanitize the CDN options
        if ($config['KANSO_USE_CDN'] === true && ! filter_var($config['KASNO_CDN_URL'], FILTER_VALIDATE_URL)) {
            $config['KANSO_USE_CDN'] = false;
            $config['KASNO_CDN_URL'] = '';
        }

        # Filter and sanitize the cahce options
        if ($config['KANSO_USE_CACHE'] === true) {
            $validCacheLife = $this->validateCacheLife($config['KANSO_CACHE_LIFE']);
            if (!$validateCacheLife) {
                $config['KANSO_USE_CACHE']  = false;
                $config['KANSO_CACHE_LIFE'] = '';
            }
            else {
                $config['KANSO_CACHE_LIFE'] = $validCacheLife;
            }
        }

        # Filter and sanitize the static pages
        if (!is_array($config['KANSO_STATIC_PAGES'])) $config['KANSO_STATIC_PAGES'] = [];

        # Filter and sanitize author pages pages
        if (!is_array($config['KANSO_AUTHOR_SLUGS'])) $config['KANSO_AUTHOR_SLUGS'] = [];

        # Filter and santize the password
        if (empty($config['KANSO_OWNER_PASSWORD'])) $config['KANSO_OWNER_PASSWORD'] = $this->defaults['KANSO_OWNER_PASSWORD'];

        # Filter and santize the email
        if (empty($config['KANSO_OWNER_EMAIL'])) $config['KANSO_OWNER_EMAIL'] = $this->defaults['KANSO_OWNER_EMAIL'];

        # Filter and santize the username
        if (empty($config['KANSO_OWNER_USERNAME'])) $config['KANSO_OWNER_USERNAME'] = $this->defaults['KANSO_OWNER_USERNAME'];

        # Filter and sanitize the table prefix
        if (empty($config['table_prefix'])) $config['table_prefix'] = $this->defaults['table_prefix'];
        $config['table_prefix'] = preg_replace('/[^a-z_-]+/', '_', strtolower($config['table_prefix']));

        # Return the config
        return $config;
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
        $format = $this->tempConfig['KANSO_PERMALINKS'];
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

    /**
     * Convert a string into a valid permalink route
     *
     * @param  string   $url    The url to be converted
     * @return array            Array with the the actual link and the route
     */
    private function createPermalinks($url) 
    {
        $permaLink = '';
        $route     = '';
        $urlPieces = explode('/', $url);
        $map = [
            'year'     => '(:year)',
            'month'    => '(:month)',
            'day'      => '(:day)',
            'hour'     => '(:hour)',
            'minute'   => '(:minute)',
            'second'   => '(:second)',
            'postname' => '(:postname)',
            'category' => '(:category)',
            'author'   => '(:author)',
        ];
        foreach ($urlPieces as $key) {
            if (isset($map[$key])) {
                $permaLink .= $key.DIRECTORY_SEPARATOR;
                $route     .= $map[$key].DIRECTORY_SEPARATOR;
            }
        }
        return [
            'KANSO_PERMALINKS' => $permaLink,
            'KANSO_PERMALINKS_ROUTE' => $route,
        ];

    }

    /**
     * Convert a list of thumbnail sizes into an array
     *
     * @param  string   $thumbnails    Comma separted list of thumbnail sizes
     * @return array                   Array of thumbnail sizes
    */
    private function createThumbnailsArray($thumbnails) 
    {
        if (is_string($thumbnails)) {
            $thumbs = ["400", "800", "1200"];
        
            if ($thumbnails !== '') {
                $thumbnails = array_map('trim', explode(',', $thumbnails));
                foreach ($thumbnails as $i => $values) {
                    if ($i > 2) return $thumbs;
                    $values = preg_replace("/[^\d+ ]/", "", $values);
                    $values = array_map('trim', explode(' ', $values));
                    if (count($values) === 1) {
                        $thumbs[$i] = $values[0];
                    }
                    else if (isset($values[1])) {
                        $thumbs[$i] = [$values[0], $values[1]];
                    }
                }
            }
        }
        
        return $thumbnails;
    }

    /**
     * Validate cache lifetime
     *
     * @param  string       $cacheLife  A cache life - e.g '3 hours'
     * @return string|bool
     */
    private function validateCacheLife($cacheLife) 
    {
        if ($cacheLife === '') return false;
        $times = ['second' => true, 'minute' => true, 'hour' => true, 'week' => true, 'day' => true, 'month' => true, 'year' => true];
        $life  = array_map('trim', explode(' ', $cacheLife));
        if (count($life) !== 2) return false;
        if (!is_numeric($life[0])) return false;
        $time = (int)$life[0];
        $life = rtrim($life[1], 's'); 

        if ($time == 0) return false;
        
        if (!isset($times[$life])) return false;

        $life = $time > 1 ? $life.'s' : $life;

        return $time.' '.$life;
    }


}