<?php

namespace Kanso\Install;

/**
 * Install Kanso
 *
 * This class is used to install Kanso or to restore an exising 
 * application to its factory settings.
 *
 */
class Installer 
{

    /**
     * @var boolean 
     */
    public $isInstalled;

    /**
     * @var string    The root directory of the Kanso installation 
     */
    private $KansoDir;

    /**
     * @var array    Associative array of installation configuration
     */
    private $userConfig;

    /**
     * @var array    Associative array of configuration options to save
     */
    private $config;

    /**
     * @var array    Default Kanso configuration options
     */
    private $defaults = [

        # Database connection settings
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => 'root',
        'dbname'   => 'Kanso',

        # Core Kanso configuration
        'KANSO_RUN_MODE'   => 'CMS',
        'KANSO_THEME_NAME' => 'Roshi',
        'KANSO_SITE_TITLE' => 'Kanso',
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
        'KANSO_AUTHOR_USERNAME'  => 'admin',
        'KANSO_AUTHOR_EMAIL'     => 'admin@example.com',
        'KANSO_AUTHOR_PASSWORD'  => 'password1',
	];

	/**
     * Constructor
     *
     */
    public function __construct()
    {
        # Set the directy where Kanso is installed
    	$this->KansoDir    = dirname(dirname(__file__));

        # Is Kanso currently installed ?
        $this->isInstalled = !file_exists($this->KansoDir.'/Install.php') && file_exists($this->KansoDir.'/Config.php');
    }

    /**
     * Install Kanso
     *
     * This method will restore/install Kanso to it's default settings
     * If $asDefault is set to true, the current Kanso application
     * will be reistalled, otherwise it is installed from settings
     * found in Install.ini.php
     *
     * @param  boolean    $asDefault  Install default factory settings
     * @return boolean
     */
    public function installKanso($asDefault = false)
    {
    	# Validate Kanso is NOT alread installed if this
        # is a fresh install
    	if (!$asDefault) {

            # Validate installation
    		if ($this->isInstalled) throw new \Exception("Could not install, Kanso is already installed.");
    		
            # get the user condig settings
            $this->userConfig = include $this->KansoDir.'/Install.php';
    		
            # Filter the config
            $this->config = $this->filterConfig();
    	}
        # Install from defaults
    	else {
            $this->config         = \Kanso\Kanso::getInstance()->Config();
            $defaults             = $this->defaults;
            $defaults['host']     = $this->config['host'];
            $defaults['user']     = $this->config['user'];
            $defaults['password'] = $this->config['password'];
            $defaults['dbname']   = $this->config['dbname'];
            $this->config         = $defaults;
    	}
    
        # The database only needs to be installed if Kanso is being used as CMS
        if ($this->config['KANSO_RUN_MODE'] === 'CMS') {

            # Do a test connection to the database to validate DB credentials are valid
            $this->DBConnect();


            # Install the Kanso database
            $this->installDB();
        }

    	# Save the config
        file_put_contents($this->KansoDir.'/Config.php', "<?php\nreturn\n".var_export($this->config, true).";?>");

    	# Delete the install file
    	if (file_exists($this->KansoDir.'/Install.php')) unlink($this->KansoDir.'/Install.php');

    	return true;
    }

    /**
     * Install The Default Kanso databse
     *
     */
    private function installDB()
    {
    	# Save the database name
        $dbname = $this->config['dbname'];

        # Start a new session if one has not already been started
        if(session_id() == '') session_start();

        # Clear the session
   		$_SESSION = [];

        # Create a new database
        $db = new \Kanso\Database\Database($this->config);

        # Delete the entire database
        $db->Query("DROP database $dbname");

        # Create a fresh database
        $db->Query("create database $dbname");

        # Re-connect to the database
        $db = new \Kanso\Database\Database($this->config);

        # Get a new CRUD
        $CRUD = new \Kanso\Database\CRUD\CRUD($db);

        # Include default Kanso Settings
        include 'KansoDefaults.php';

        # Create new tables
        $CRUD->CREATE_TABLE('posts', $KANSO_DEFAULTS_POSTS_TABLE);

        $CRUD->CREATE_TABLE('tags', $KANSO_DEFAULTS_TAGS_TABLE);

        $CRUD->CREATE_TABLE('categories', $KANSO_DEFAULTS_CATEGORIES_TABLE);

        $CRUD->CREATE_TABLE('authors', $KANSO_DEFAULTS_AUTHORS_TABLE);

        $CRUD->CREATE_TABLE('comments', $KANSO_DEFAULTS_COMMENTS_TABLE);

        $CRUD->CREATE_TABLE('tags_to_posts', $KANSO_DEFAULTS_TAGS_TO_POSTS_TABLE);

        $CRUD->CREATE_TABLE('content_to_posts', $KANSO_DEFAULTS_CONTENT_TO_POSTS_TABLE);

        $CRUD->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        $CRUD->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('tag_id')->ADD_FOREIGN_KEY('tags', 'id');

        $CRUD->ALTER_TABLE('posts')->MODIFY_COLUMN('category_id')->ADD_FOREIGN_KEY('categories', 'id');
        
        $CRUD->ALTER_TABLE('posts')->MODIFY_COLUMN('author_id')->ADD_FOREIGN_KEY('authors', 'id');

        $CRUD->ALTER_TABLE('comments')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');

        $CRUD->ALTER_TABLE('content_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        
        # Populate tables

        # Default Author
        $CRUD->INSERT_INTO('authors')->VALUES($KANSO_DEFAULT_AUTHOR)->QUERY();

        # Default Tags
        foreach ($KANSO_DEFAULT_TAGS as $i => $tag) {
            $CRUD->INSERT_INTO('tags')->VALUES($tag)->QUERY();
        }

        # Default categories
        foreach ($KANSO_DEFAULT_CATEGORIES as $i => $category) {
            $CRUD->INSERT_INTO('categories')->VALUES($category)->QUERY();
        }        

        # Default Articles
        foreach ($KANSO_DEFAULT_ARTICLES as $i => $article) {
            $CRUD->INSERT_INTO('posts')->VALUES($article)->QUERY();
            foreach ($KANSO_DEFAULT_TAGS as $t => $tag) {
                # skip untagged
                if ($t === 0) continue;
                $CRUD->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $i+1, 'tag_id' => $t+1])->QUERY();
            }
            $CRUD->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $i+1, 'content' => $KANSO_DEFAULT_ARTICLE_CONTENT[$i]])->QUERY();
        }
        
        # Default comments
        foreach ($KANSO_DEFAULT_COMMENTS as $comment) {
            $CRUD->INSERT_INTO('comments')->VALUES($comment)->QUERY();
        }

    }

    /**
     * Do a test connection to the database
     *
     * @throws PDOException
     */
    private function DBConnect()
	{
		$dsn = 'mysql:dbname='.$this->config["dbname"].';host='.$this->config["host"].'';
		try 
		{
			# Read settings from INI file, set UTF8
			$pdo = new \PDO($dsn, $this->config["user"], $this->config["password"], [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
			
			# We can now log any exceptions on Fatal error. 
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			
			# Disable emulation of prepared statements, use REAL prepared statements instead.
			$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

		}
		catch (PDOException $e) 
		{
			throw new \Exception("Kanso Install Error. Could not connect to database.");
			die();
		}
	}

    /**
     * Filter and sanitize the configuration to unsure Kanso will run
     * 
     * @return array
     */
    private function filterConfig()
    {
    	# Merge the config with the defaults
    	$config = array_merge($this->defaults, $this->userConfig);

    	# Filter and sanitize the config
    	$config['host'] 		     		= filter_var($config['host'], FILTER_SANITIZE_STRING);
    	$config['user'] 			 		= filter_var($config['user'], FILTER_SANITIZE_STRING);
    	$config['password']		     		= filter_var($config['password'], FILTER_SANITIZE_STRING);
    	$config['dbname'] 		     		= filter_var($config['dbname'], FILTER_SANITIZE_STRING);
        $config['KANSO_RUN_MODE']           = strtoupper(filter_var($config['KANSO_RUN_MODE'], FILTER_SANITIZE_STRING));
    	$config['KANSO_THEME_NAME']  		= filter_var($config['KANSO_THEME_NAME'], FILTER_SANITIZE_STRING);
    	$config['KANSO_SITE_TITLE']  		= filter_var($config['KANSO_SITE_TITLE'], FILTER_SANITIZE_STRING);
        $config['KANSO_SITE_DESCRIPTION']   = filter_var($config['KANSO_SITE_DESCRIPTION'], FILTER_SANITIZE_STRING);
    	$config['KANSO_SITEMAP']	 		= filter_var($config['KANSO_SITEMAP'], FILTER_SANITIZE_STRING);
    	$config['KANSO_PERMALINKS']	 		= filter_var($config['KANSO_PERMALINKS'], FILTER_SANITIZE_STRING);
    	$config['KANSO_PERMALINKS_ROUTE']	= filter_var($config['KANSO_PERMALINKS_ROUTE'], FILTER_SANITIZE_STRING);
    	$config['KANSO_POSTS_PER_PAGE']		= (int) $config['KANSO_POSTS_PER_PAGE'];
    	$config['KANSO_ROUTE_TAGS']	 		= (bool) $config['KANSO_ROUTE_TAGS'];
    	$config['KANSO_ROUTE_CATEGORIES']	= (bool) $config['KANSO_ROUTE_CATEGORIES'];
    	$config['KANSO_ROUTE_AUTHORS']	    = (bool) $config['KANSO_ROUTE_AUTHORS'];
    	$config['KANSO_THUMBNAILS']	        = $config['KANSO_THUMBNAILS'];
    	$config['KANSO_IMG_QUALITY']		= (int) $config['KANSO_IMG_QUALITY'];
    	$config['KANSO_USE_CDN']		    = (bool) $config['KANSO_USE_CDN'];
    	$config['KASNO_CDN_URL']		    = filter_var($config['KASNO_CDN_URL'], FILTER_SANITIZE_STRING);
    	$config['KANSO_USE_CACHE']			= (bool) $config['KANSO_USE_CACHE'];
    	$config['KANSO_CACHE_LIFE']			= filter_var($config['KANSO_CACHE_LIFE'], FILTER_SANITIZE_STRING);
    	$config['KANSO_COMMENTS_OPEN']		= (bool) $config['KANSO_COMMENTS_OPEN'];
    	$config['KANSO_AUTHOR_USERNAME']	= filter_var($config['KANSO_AUTHOR_USERNAME'], FILTER_SANITIZE_STRING);
    	$config['KANSO_AUTHOR_EMAIL']		= filter_var($config['KANSO_AUTHOR_EMAIL'], FILTER_SANITIZE_STRING);
    	$config['KANSO_AUTHOR_PASSWORD']	= filter_var($config['KANSO_AUTHOR_PASSWORD'], FILTER_SANITIZE_STRING);
        $config['KANSO_STATIC_PAGES']       = $config['KANSO_STATIC_PAGES'];
        $config['KANSO_AUTHOR_SLUGS']       = $config['KANSO_AUTHOR_SLUGS'];

        # Filter and sanitize the run mode
        if ($config['KANSO_RUN_MODE'] !== 'CMS' && $config['KANSO_RUN_MODE'] !== 'FRAMEWORK') $config['KANSO_RUN_MODE'] = 'CMS';

    	# Filter the sanitize the sitemap
    	if (strpos($config['KANSO_SITEMAP'], '.') === false) $config['KANSO_SITEMAP'] = $this->defaults['KANSO_SITEMAP'];

    	# Fiter and sanitize the permalinks
    	$permalinks = $this->filterPermalinks($config['KANSO_PERMALINKS']);
    	if (empty($permalinks['KANSO_PERMALINKS']) || empty($permalinks['KANSO_PERMALINKS_ROUTE'])) {
    		$config['KANSO_PERMALINKS_ROUTE'] = $this->defaults['KANSO_PERMALINKS_ROUTE'];
    		$config['KANSO_PERMALINKS'] 	  = $this->defaults['KANSO_PERMALINKS'];
    	}

    	# Fiter and sanitize the posts per page
    	if ($config['KANSO_POSTS_PER_PAGE']	<= 0) $config['KANSO_POSTS_PER_PAGE'] = $this->defaults['KANSO_POSTS_PER_PAGE'];
    	
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
    	if (empty($config['KANSO_AUTHOR_PASSWORD'])) $config['KANSO_AUTHOR_PASSWORD'] = $this->defaults['KANSO_AUTHOR_PASSWORD'];

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
            'KANSO_PERMALINKS' 		 => $permaLink,
            'KANSO_PERMALINKS_ROUTE' => $route,
        ];
    }

}