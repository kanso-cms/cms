<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Install;

use PDO;
use PDOException;
use RuntimeException;
use Closure;
use Kanso\Framework\Config\Config;
use Kanso\Framework\Database\Database;
use Kanso\Framework\Http\Request\Request;
use Kanso\Framework\Http\Response\Response;

/**
 * CMS installer
 *
 * @author Joe J. Howard
 */
class Installer 
{
    /**
     * Database Connection
     *
     * @var \Kanso\Framework\Database\Database
     */
    private $database;

    /**
     * Database Connection
     *
     * @var \Kanso\Framework\Config\Config
     */
    private $config;

    /**
     * The path to "Install.php"
     *
     * @var string
     */
    private $installPath;

    /**
     *  Is kanso installed?
     *
     * @var bool
     */
    private $isInstalled;

	/**
     * Constructor
     *
     * @access public
     */
    public function __construct(Config $config, Database $database, string $installPath)
    {
        $this->config = $config;

        $this->database = $database;

        $this->installPath = $installPath.DIRECTORY_SEPARATOR.'Install.php';

        $this->isInstalled();
    }

    /**
     * Returns TRUE if Kanso is installed or FALSE if it is not
     *
     * @access public
     * @return bool
     */
    public function isInstalled(): bool
    {
        if (!is_bool($this->isInstalled))
        {
            $this->isInstalled = !file_exists($this->installPath);
        }

        return $this->isInstalled;
    }

    /**
     * Install the CMS
     *
     * @access public
     * @param  \Kanso\Framework\Http\Request\Request   $request  Framework Request instance
     * @param  \Kanso\Framework\Http\Response\Response $response Framework Response instance
     * @param  \Closure                                $next     Next middleware layer
     * @param  string                                  $pageType The page type being loaded
     */
    public function run(Request $request, Response $response, Closure $next)
    {
        # Validate installation
        if ($this->isInstalled)
        {
            throw new RuntimeException("Could not install Kanso. Kanso is already installed. If you want to reinstall it, use the <code>reInstall()</code> method.");
        }

        # Load the Kanso settings
        $settings = $this->config->get('cms');

        # Install the Kanso database
        $this->installDB();

        # Delete the install file
        unlink($this->installPath);
        
        $next();
    }

    /**
     * Show the install splash
     *
     * @access public
     * @param  \Kanso\Framework\Http\Request\Request   $request  Framework Request instance
     * @param  \Kanso\Framework\Http\Response\Response $response Framework Response instance
     * @param  \Closure                                $next     Next middleware layer
     * @param  string                                  $pageType The page type being loaded
     */
    public function display(Request $request, Response $response, Closure $next)
    {
        # Set appropriate content type header
        $response->format()->set('text/html');

        # Set the response body
        $response->body()->set($response->view()->display(dirname(__FILE__).'/Views/installed.php'));
        
        # Set the status
        $response->status()->set(200);

        # Disable the cache
        $response->cache()->disable();

        # destroy the cookie
        $response->cookie()->destroy();

        # destroy the session
        $response->session()->destroy();
    }

    /**
     * Reinstall Kanso to defaults but keep database and admin panel login credentials
     *
     * @access public
     */
    public function reInstall()
    {
        # Restore default configuration
        $this->config->set('cms', $this->config->getDefault('cms'));

        # Install the Kanso database
        $this->installDB();

        return true;
    }

    /**
     * Install the Kanso database
     *
     * @access private
     * @return NULL
     */
    private function installDB()
    {
    	# Save the database name
        $dbname = $this->config->get('database.configurations.'.$this->config->get('database.default').'.name');

        # Create the default database
        $SQL = $this->database->create()->builder();

        # Include default Kanso Settings
        include 'databaseDefaults.php';

        # Create new tables
        $SQL->CREATE_TABLE('posts', $KANSO_DEFAULTS_POSTS_TABLE);

        $SQL->CREATE_TABLE('tags', $KANSO_DEFAULTS_TAGS_TABLE);

        $SQL->CREATE_TABLE('categories', $KANSO_DEFAULTS_CATEGORIES_TABLE);

        $SQL->CREATE_TABLE('users', $KANSO_DEFAULTS_USERS_TABLE);

        $SQL->CREATE_TABLE('comments', $KANSO_DEFAULTS_COMMENTS_TABLE);

        $SQL->CREATE_TABLE('tags_to_posts', $KANSO_DEFAULTS_TAGS_TO_POSTS_TABLE);

        $SQL->CREATE_TABLE('content_to_posts', $KANSO_DEFAULTS_CONTENT_TO_POSTS_TABLE);

        $SQL->CREATE_TABLE('media_uploads', $KANSO_DEFAULTS_MEDIA_TABLE);

        $SQL->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        $SQL->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('tag_id')->ADD_FOREIGN_KEY('tags', 'id');

        $SQL->ALTER_TABLE('posts')->MODIFY_COLUMN('category_id')->ADD_FOREIGN_KEY('categories', 'id');
        
        $SQL->ALTER_TABLE('posts')->MODIFY_COLUMN('author_id')->ADD_FOREIGN_KEY('users', 'id');

        # No foreign keys here so that you can delete
        # an attachment without having a constraint
        $SQL->ALTER_TABLE('comments')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        $SQL->ALTER_TABLE('content_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        
        # Populate tables

        # Default Tags
        foreach ($KANSO_DEFAULT_TAGS as $i => $tag)
        {
            $SQL->INSERT_INTO('tags')->VALUES($tag)->QUERY();
        }

        # Default categories
        foreach ($KANSO_DEFAULT_CATEGORIES as $i => $category)
        {
            $SQL->INSERT_INTO('categories')->VALUES($category)->QUERY();
        }

        # Default media
        foreach ($KANSO_DEFAULT_IMAGES as $image)
        {
            $SQL->INSERT_INTO('media_uploads')->VALUES($image)->QUERY();
        }

        # Default user
        $SQL->INSERT_INTO('users')->VALUES($KANSO_DEFAULT_USER)->QUERY();

        # Default Articles
        foreach ($KANSO_DEFAULT_ARTICLES as $i => $article)
        {
            $SQL->INSERT_INTO('posts')->VALUES($article)->QUERY();
            
            foreach ($KANSO_DEFAULT_TAGS as $t => $tag)
            {
                # skip untagged
                if ($t === 0) continue;
                
                $SQL->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $i+1, 'tag_id' => $t+1])->QUERY();
            }
            
            $SQL->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $i+1, 'content' => $KANSO_DEFAULT_ARTICLE_CONTENT[$i]])->QUERY();
        }
        
        # Default comments
        foreach ($KANSO_DEFAULT_COMMENTS as $comment)
        {
            $SQL->INSERT_INTO('comments')->VALUES($comment)->QUERY();
        }
    }
}
