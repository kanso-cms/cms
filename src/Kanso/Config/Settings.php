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
     * @var array    Default Kanso configuration options
     */
    private $defaults = [

        # Database connection settings
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => 'root',
        'dbname'   => 'Kanso',
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
     * @var string    Path to config file
     */
    private $configPath;

    /**
     * Private constructor
     */
    public function __construct()
    {
        $this->configPath = realpath(__DIR__ . '/..').DIRECTORY_SEPARATOR.'Config.php';
        $this->parse();
    }

    public function parse()
    {
        if (file_exists($this->configPath)) $config = include($this->configPath);
        if ($config) {
            $this->configData = $config;
            return $config;
        }
        $this->configData = $this->defaults;
        return $this->defaults;
    }

    public function put($key, $value)
    {
       $this->configData[$key] = $value;
       $this->save();
    }

    public function putMultiple($data)
    {
        foreach ($data as $key => $value) {
            $this->configData[$key] = $value;
        }
        $this->save();
    }

    public function get($key = null)
    {
        if (!$key) return $this->configData;
        if ($this->has($key)) return $this->configData[$key];
        return false;
    }

    public function remove($key)
    {
       if ($this->has($key)) unset($this->configData[$key]);
       $this->save();
    }

    public function has($key)
    {
       return array_key_exists($key, $this->configData);
    }

    public function refresh()
    {
        return $this->parse();
    }

    private function save()
    {
        # Fire the event
        \Kanso\Events::fire('configChange', $this->configData);

        # Filter the config internally
        $this->configData = $this->filterConfig();

        # Filter the config
        $this->configData = \Kanso\Filters::apply('configChange', $this->configData);

        # Encode and save the config
        file_put_contents($this->configPath, "<?php\nreturn\n".var_export($this->configData, true).";?>");

        return true;
    }

    /**
     * Filter and sanitize the configuration to unsure Kanso will run
     * 
     * @return array
     */
    private function filterConfig()
    {
        # Merge the config with the defaults
        $config = array_merge($this->defaults, $this->configData);

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
        $config['KANSO_ROUTE_TAGS']         = (bool) $config['KANSO_ROUTE_TAGS'];
        $config['KANSO_ROUTE_CATEGORIES']   = (bool) $config['KANSO_ROUTE_CATEGORIES'];
        $config['KANSO_ROUTE_AUTHORS']      = (bool) $config['KANSO_ROUTE_AUTHORS'];
        $config['KANSO_THUMBNAILS']         = $config['KANSO_THUMBNAILS'];
        $config['KANSO_IMG_QUALITY']        = (int) $config['KANSO_IMG_QUALITY'];
        $config['KANSO_USE_CDN']            = (bool) $config['KANSO_USE_CDN'];
        $config['KASNO_CDN_URL']            = filter_var($config['KASNO_CDN_URL'], FILTER_SANITIZE_STRING);
        $config['KANSO_USE_CACHE']          = (bool) $config['KANSO_USE_CACHE'];
        $config['KANSO_CACHE_LIFE']         = filter_var($config['KANSO_CACHE_LIFE'], FILTER_SANITIZE_STRING);
        $config['KANSO_COMMENTS_OPEN']      = (bool) $config['KANSO_COMMENTS_OPEN'];
        $config['KANSO_OWNER_USERNAME']     = filter_var($config['KANSO_OWNER_USERNAME'], FILTER_SANITIZE_STRING);
        $config['KANSO_OWNER_EMAIL']        = filter_var($config['KANSO_OWNER_EMAIL'], FILTER_SANITIZE_STRING);
        $config['KANSO_OWNER_PASSWORD']     = filter_var($config['KANSO_OWNER_PASSWORD'], FILTER_SANITIZE_STRING);
        $config['KANSO_STATIC_PAGES']       = $config['KANSO_STATIC_PAGES'];
        $config['KANSO_AUTHOR_SLUGS']       = $config['KANSO_AUTHOR_SLUGS'];

        
        # Filter the sanitize the sitemap
        if (strpos($config['KANSO_SITEMAP'], '.') === false) $config['KANSO_SITEMAP'] = $this->defaults['KANSO_SITEMAP'];

        # Fiter and sanitize the permalinks
        $permalinks = $this->filterPermalinks($config['KANSO_PERMALINKS']);
        if (empty($permalinks['KANSO_PERMALINKS']) || empty($permalinks['KANSO_PERMALINKS_ROUTE'])) {
            $config['KANSO_PERMALINKS_ROUTE'] = $this->defaults['KANSO_PERMALINKS_ROUTE'];
            $config['KANSO_PERMALINKS']       = $this->defaults['KANSO_PERMALINKS'];
        }

        # Fiter and sanitize the posts per page
        if ($config['KANSO_POSTS_PER_PAGE'] <= 0) $config['KANSO_POSTS_PER_PAGE'] = $this->defaults['KANSO_POSTS_PER_PAGE'];
        
        # Fiter and sanitize the thumbnail sizes
        $config['KANSO_THUMBNAILS'] = array_map('intval', $config['KANSO_THUMBNAILS']);

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
     * Validate and filter the cache life
     * 
     * @param  string $cacheLife    e.g '-1 Week'
     * @return string|boolean
     */
    private static function validateCacheLife($cacheLife) 
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

    /**
     * Validate and filter the permalinks path
     * 
     * @param  string $url
     * @return array
     */
    private function filterPermalinks($url)
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
            'KANSO_PERMALINKS'       => $permaLink,
            'KANSO_PERMALINKS_ROUTE' => $route,
        ];
    }



}