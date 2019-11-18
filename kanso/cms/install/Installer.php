<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\install;

use Closure;
use kanso\cms\access\Access;
use kanso\framework\config\Config;
use kanso\framework\database\Database;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\exceptions\NotFoundException;
use kanso\framework\http\response\Response;
use RuntimeException;

/**
 * CMS installer.
 *
 * @author Joe J. Howard
 */
class Installer
{
    /**
     * Database Connection.
     *
     * @var \kanso\framework\database\Database
     */
    private $database;

    /**
     * Database Connection.
     *
     * @var \kanso\framework\config\Config
     */
    private $config;

    /**
     * Access manager.
     *
     * @var \kanso\cms\access\Access
     */
    private $access;

    /**
     * The path to "Install.php".
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
     * Constructor.
     *
     * @param \kanso\framework\config\Config     $config   Config manager
     * @param \kanso\framework\database\Database $database Database manager
     * @param \kanso\cms\access\Access           $access   Access module
     */
    public function __construct(Config $config, Database $database, Access $access, string $installPath)
    {
        $this->config = $config;

        $this->database = $database;

        $this->access = $access;

        $this->installPath = $installPath . DIRECTORY_SEPARATOR . 'install.php';

        if (file_exists($installPath . DIRECTORY_SEPARATOR . 'install.sample.php'))
        {
            throw new NotFoundException('Could not install Kanso. You need to rename the "install.sample.php" to "install.php".');
        }

        $this->isInstalled();
    }

    /**
     * Returns TRUE if Kanso is installed or FALSE if it is not.
     *
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
     * Install the CMS.
     *
     * @param \kanso\framework\http\request\Request   $request  Framework Request instance
     * @param \kanso\framework\http\response\Response $response Framework Response instance
     * @param \Closure                                $next     Next middleware layer
     */
    public function run(Request $request, Response $response, Closure $next): void
    {
        // Validate installation
        if ($this->isInstalled)
        {
            throw new RuntimeException('Could not install Kanso. Kanso is already installed. If you want to reinstall it, use the <code>reInstall()</code> method.');
        }

        // Install the Kanso database
        $this->installDB();

        // Create robots.txt
        $this->installRobots();

        // Delete the install file
        unlink($this->installPath);

        $next();
    }

    /**
     * Show the install splash.
     *
     * @param \kanso\framework\http\request\Request   $request  Framework Request instance
     * @param \kanso\framework\http\response\Response $response Framework Response instance
     * @param \Closure                                $next     Next middleware layer
     */
    public function display(Request $request, Response $response, Closure $next): void
    {
        // Set appropriate content type header
        $response->format()->set('text/html');

        // Set the response body
        $response->body()->set($response->view()->display(dirname(__FILE__) . '/views/installed.php'));

        // Set the status
        $response->status()->set(200);

        // Disable the cache
        $response->disableCaching();

        // destroy the cookie
        $response->cookie()->destroy();

        // destroy the session
        $response->session()->destroy();

        // Start a new session
        $response->session()->start();
    }

    /**
     * Reinstall Kanso to defaults but keep database and admin panel login credentials.
     */
    public function reInstall()
    {
        // Restore default configuration
        $this->config->set('cms', $this->config->getDefault('cms'));

        // Install the Kanso database
        $this->installDB();

        return true;
    }

    /**
     * Install the Kanso database.
     *
     * @suppress PhanUndeclaredVariable
     */
    private function installDB(): void
    {
    	// Save the database name
        $dbname = $this->config->get('database.configurations.' . $this->config->get('database.default') . '.name');

        // Create the default database
        $SQL = $this->database->create()->builder();

        // Include default Kanso Settings
        include 'databaseDefaults.php';

        // Create new tables
        $SQL->CREATE_TABLE('posts', $KANSO_DEFAULTS_POSTS_TABLE);

        $SQL->CREATE_TABLE('tags', $KANSO_DEFAULTS_TAGS_TABLE);

        $SQL->CREATE_TABLE('categories', $KANSO_DEFAULTS_CATEGORIES_TABLE);

        $SQL->CREATE_TABLE('users', $KANSO_DEFAULTS_USERS_TABLE);

        $SQL->CREATE_TABLE('comments', $KANSO_DEFAULTS_COMMENTS_TABLE);

        $SQL->CREATE_TABLE('tags_to_posts', $KANSO_DEFAULTS_TAGS_TO_POSTS_TABLE);

        $SQL->CREATE_TABLE('categories_to_posts', $KANSO_DEFAULTS_CATEGORIES_TO_POSTS_TABLE);

        $SQL->CREATE_TABLE('content_to_posts', $KANSO_DEFAULTS_CONTENT_TO_POSTS_TABLE);

        $SQL->CREATE_TABLE('media_uploads', $KANSO_DEFAULTS_MEDIA_TABLE);

        $SQL->CREATE_TABLE('post_meta', $KANSO_DEFAULTS_POST_META_TABLE);

        $SQL->CREATE_TABLE('crm_visitors', $KANSO_DEFAULTS_VISITORS_TABLE);

        $SQL->CREATE_TABLE('crm_visits', $KANSO_DEFAULTS_VISITS_TABLE);

        $SQL->CREATE_TABLE('crm_visit_actions', $KANSO_DEFAULTS_VISIT_ACTIONS_TABLE);

        $SQL->CREATE_TABLE('payment_tokens', $KANSO_DEFAULTS_PAYMENT_TOKENS_TABLE);

        $SQL->CREATE_TABLE('shopping_cart_items', $KANSO_DEFAULTS_SHOPPING_CART_TABLE);

        $SQL->CREATE_TABLE('shipping_addresses', $KANSO_DEFAULTS_SHIPPING_ADDRESS_TABLE);

        $SQL->CREATE_TABLE('transactions', $KANSO_DEFAULTS_TRANSACTION_TABLE);

        $SQL->CREATE_TABLE('loyalty_points', $KANSO_DEFAULTS_LOYALTY_POINTS_TABLE);

        $SQL->CREATE_TABLE('loyalty_coupons', $KANSO_DEFAULTS_LOYALTY_COUPONS_TABLE);

        $SQL->CREATE_TABLE('used_public_coupons', $KANSO_DEFAULTS_USED_PUBLIC_COUPONS);

        $SQL->CREATE_TABLE('product_reviews', $KANSO_DEFAULTS_PRODUCT_REVIEWS_TABLE);

        $SQL->CREATE_TABLE('product_review_votes', $KANSO_DEFAULTS_PRODUCT_REVIEW_VOTES_TABLE);

        $SQL->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        $SQL->ALTER_TABLE('tags_to_posts')->MODIFY_COLUMN('tag_id')->ADD_FOREIGN_KEY('tags', 'id');

        $SQL->ALTER_TABLE('categories_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        $SQL->ALTER_TABLE('categories_to_posts')->MODIFY_COLUMN('category_id')->ADD_FOREIGN_KEY('categories', 'id');

        $SQL->ALTER_TABLE('posts')->MODIFY_COLUMN('author_id')->ADD_FOREIGN_KEY('users', 'id');

        // No foreign keys here so that you can delete
        // an attachment without having a constraint
        $SQL->ALTER_TABLE('comments')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');
        $SQL->ALTER_TABLE('content_to_posts')->MODIFY_COLUMN('post_id')->ADD_FOREIGN_KEY('posts', 'id');

        // Populate tables

        // Default Tags
        foreach ($KANSO_DEFAULT_TAGS as $i => $tag)
        {
            $SQL->INSERT_INTO('tags')->VALUES($tag)->QUERY();
        }

        // Default categories
        foreach ($KANSO_DEFAULT_CATEGORIES as $i => $category)
        {
            $SQL->INSERT_INTO('categories')->VALUES($category)->QUERY();
        }

        // Default media
        foreach ($KANSO_DEFAULT_IMAGES as $image)
        {
            $SQL->INSERT_INTO('media_uploads')->VALUES($image)->QUERY();
        }

        // Default user
        $SQL->INSERT_INTO('users')->VALUES($KANSO_DEFAULT_USER)->QUERY();

        // Default Articles
        foreach ($KANSO_DEFAULT_ARTICLES as $i => $article)
        {
            if (isset($article['meta']))
            {
                $meta = $article['meta'];

                unset($article['meta']);

                $SQL->INSERT_INTO('post_meta')->VALUES(['post_id' => $i+1, 'content' => serialize($meta)])->QUERY();
            }

            $SQL->INSERT_INTO('posts')->VALUES($article)->QUERY();

            foreach ($KANSO_DEFAULT_TAGS as $t => $tag)
            {
                // skip untagged
                if ($t === 0)
                {
                    continue;
                }

                $SQL->INSERT_INTO('tags_to_posts')->VALUES(['post_id' => $i+1, 'tag_id' => $t+1])->QUERY();
            }

            foreach ($KANSO_DEFAULT_CATEGORIES as $j => $tag)
            {
                // skip uncategorized
                if ($j === 0)
                {
                    continue;
                }

                $SQL->INSERT_INTO('categories_to_posts')->VALUES(['post_id' => $i+1, 'category_id' => $j+1])->QUERY();
            }

            $SQL->INSERT_INTO('content_to_posts')->VALUES(['post_id' => $i+1, 'content' => $KANSO_DEFAULT_ARTICLE_CONTENT[$i]])->QUERY();
        }

        // Default comments
        foreach ($KANSO_DEFAULT_COMMENTS as $comment)
        {
            $SQL->INSERT_INTO('comments')->VALUES($comment)->QUERY();
        }
    }

    /**
     * Create the robots.txt file.
     */
    private function installRobots(): void
    {
        $enabled = $this->config->get('cms.security.enable_robots');
        $content = $this->config->get('cms.security.robots_text_content');

        if (!$enabled)
        {
            $this->access->saveRobots($this->access->blockAllRobotsText());
        }
        elseif ($enabled && empty($content))
        {
            $this->access->saveRobots($this->access->defaultRobotsText());
        }
        else
        {
            $this->access->saveRobots($content);
        }
    }
}
