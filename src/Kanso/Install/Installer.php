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
     * @var array    The current or new Config
     */
    private $config;

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

            $settings = new \Kanso\Config\Settings(true);
            $config   = $settings->get();

    	}

        # Install from defaults but keep database and admin settings
    	else {

            $current  = \Kanso\Kanso::getInstance()->Config;
            $defaults = \Kanso\Kanso::getInstance()->Settings->defaults;
            $userData = [
                'KANSO_OWNER_USERNAME'  => $current['KANSO_OWNER_USERNAME'],
                'KANSO_OWNER_EMAIL'     => $current['KANSO_OWNER_EMAIL'],
                'KANSO_OWNER_PASSWORD'  => $current['KANSO_OWNER_PASSWORD'],
                'host'         => $current['host'],
                'user'         => $current['user'],
                'password'     => $current['password'],
                'dbname'       => $current['dbname'],
                'table_prefix' => $current['table_prefix'],
            ];
            $config = array_merge($defaults, $userData);
    	}

        $this->config = $config;
    
        # Do a test connection to the database to validate DB credentials are valid
        $this->DBConnect();

        # Install the Kanso database
        $this->installDB();

    	# Save the config
        file_put_contents($this->KansoDir.'/Config.php', "<?php\nreturn\n".var_export($config, true).";?>");

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

        # Get a new Query Builder
        $Query = new \Kanso\Database\Query\Builder($db);

        # Include default Kanso Settings
        include 'KansoDefaults.php';

        # Create new tables
        $Query->CREATE_TABLE('posts', $KANSO_DEFAULTS_POSTS_TABLE);

        $Query->CREATE_TABLE('tags', $KANSO_DEFAULTS_TAGS_TABLE);

        $Query->CREATE_TABLE('categories', $KANSO_DEFAULTS_CATEGORIES_TABLE);

        $Query->CREATE_TABLE('users', $KANSO_DEFAULTS_USERS_TABLE);

        $Query->CREATE_TABLE('comments', $KANSO_DEFAULTS_COMMENTS_TABLE);

        $Query->CREATE_TABLE('tags_to_posts', $KANSO_DEFAULTS_TAGS_TO_POSTS_TABLE);

        $Query->CREATE_TABLE('content_to_posts', $KANSO_DEFAULTS_CONTENT_TO_POSTS_TABLE);

        $Query->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        $Query->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('tag_id')->ADD_FOREIGN_KEY('tags', 'id');

        $Query->ALTER_TABLE('posts')->MODIFY_COLUMN('category_id')->ADD_FOREIGN_KEY('categories', 'id');
        
        $Query->ALTER_TABLE('posts')->MODIFY_COLUMN('author_id')->ADD_FOREIGN_KEY('users', 'id');

        $Query->ALTER_TABLE('comments')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');

        $Query->ALTER_TABLE('content_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        
        # Populate tables

        # Default user
        $Query->INSERT_INTO('users')->VALUES($KANSO_DEFAULT_USER)->QUERY();

        # Default Tags
        foreach ($KANSO_DEFAULT_TAGS as $i => $tag) {
            $Query->INSERT_INTO('tags')->VALUES($tag)->QUERY();
        }

        # Default categories
        foreach ($KANSO_DEFAULT_CATEGORIES as $i => $category) {
            $Query->INSERT_INTO('categories')->VALUES($category)->QUERY();
        }        

        # Default Articles
        foreach ($KANSO_DEFAULT_ARTICLES as $i => $article) {
            $Query->INSERT_INTO('posts')->VALUES($article)->QUERY();
            foreach ($KANSO_DEFAULT_TAGS as $t => $tag) {
                # skip untagged
                if ($t === 0) continue;
                $Query->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $i+1, 'tag_id' => $t+1])->QUERY();
            }
            $Query->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $i+1, 'content' => $KANSO_DEFAULT_ARTICLE_CONTENT[$i]])->QUERY();
        }
        
        # Default comments
        foreach ($KANSO_DEFAULT_COMMENTS as $comment) {
            $Query->INSERT_INTO('comments')->VALUES($comment)->QUERY();
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

}