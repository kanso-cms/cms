<?php

namespace Kanso\Admin\Models;

/**
 * Admin User Manager
 *
 * This class has as a number of utility helper functions
 * for managing users from within the admin panel.
 *
 */
class Ajax
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Update administrator settings
     *
     * This function updates the user's administrator settings.
     * i.e username, email and password.
     *
     * @param  $username       string
     * @param  $email          string
     * @param  $password       string
     * @return string|boolean
     */
    public function updateAccountDetails($username, $email, $password, $emailNotifications = true) 
    {
        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');

        # Validate that the username/ email doesn't exist already
        # only if the user has changed either value
        if ($email !== $sessionRow['email']) {
            $emailExists = $Query->SELECT('*')->FROM('users')->WHERE('email', '=', $email)->FIND();
            if ($emailExists) return 'email_exists';
        }
        if ($username !== $sessionRow['username']) {
            $usernameExists = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->FIND();
            if ($usernameExists) return 'username_exists';
        }

        # Grab the user's row from the database
        $userRow = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $sessionRow['username'])->AND_WHERE('email', '=', $sessionRow['email'])->AND_WHERE('status', '=', 'confirmed')->FIND();
        if (!$userRow || empty($userRow)) return false;

        # Sanitize email notifications
        if ($emailNotifications === 'true' || $emailNotifications === 1 || $emailNotifications === true) {
            $emailNotifications = true;
        }
        else {
            $emailNotifications = false;
        }

        # Update the username and email
        $row = [
            'username' => $username,
            'email'    => $email,
            'email_notifications' => $emailNotifications,
        ];

        # If they changed their password lets update it
        if ($password !== '' && !empty($password)) $row['hashed_pass'] = utf8_encode(\Kanso\Security\Encrypt::encrypt($password));

        # Save to the databse and refresh the client's session
        $update = $Query->UPDATE('users')->SET($row)->WHERE('id', '=', $userRow['id'])->QUERY();

        # If updated
        if ($update) {

            # Relog the client in
            \Kanso\Kanso::getInstance()->Gatekeeper->logClientIn(array_merge($userRow, $row));

            return "valid";
        }

        return false;
    }

    /**
     * Update Author details
     *
     * @param  $name        string
     * @param  $slug        string
     * @param  $facebook    string
     * @param  $twitter     string
     * @param  $google      string
     * @return string|boolean
     */
    public function updateAuthorDetails($name, $slug, $bio, $facebook, $twitter, $google) 
    {

        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');

        # Grab the Row and update settings
        $userRow   = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $sessionRow['username'])->FIND();
        if (!$userRow) return false;

        # Change authors details
        $oldSlug  = $userRow['slug'];
        $userRow['name']        = $name;
        $userRow['slug']        = $slug;
        $userRow['facebook']    = $facebook;
        $userRow['twitter']     = $twitter;
        $userRow['gplus']       = $google;
        $userRow['description'] = $bio;

        # Save to the databse and refresh the client's session
        $update = $Query->UPDATE('users')->SET($userRow)->WHERE('id', '=', $userRow['id'])->QUERY();
        
        # Id updated
        if ($update) {

            # Relog the client in
            \Kanso\Kanso::getInstance()->Gatekeeper->logClientIn($userRow);

            # Remove the old slug
            $this->removeAuthorSlug($oldSlug);

            # Add the new one
            $this->addAuthorSlug($slug);

            return 'valid';
        }

        return false;
    }

    /**
     * Update Kanso core settings/config
     *
     * @param  $settings    array    Associative array of setting/config values
     *                               must Contain the following array(
     *                                   'route-authors'     => 'required|boolean',
     *                                   'route-categories'  => 'required|boolean',
     *                                   'route-tags'        => 'required|boolean',
     *                                   'use-CDN'           => 'required|boolean',
     *                                   'use-cache'         => 'required|boolean',
     *                                   'enable-comments'   => 'required|boolean',
     *                                   'posts-per-page'    => 'required|integer',
     *                                   'thumbnail-quality' => 'required|integer',
     *                                   'CDN-url'           => 'max_len,100',
     *                                   'cache-life'        => 'max_len,50',
     *                                   'site-title'        => 'required|max_len,100',
     *                                   'sitemap-url'       => 'required|max_len,100',
     *                                   'theme'             => 'required|max_len,100',
     *                                   'thumbnail-sizes'   => 'required|max_len,100',
     *                                   'permalinks'        => 'required|max_len,50',
     *                               );
     *
     * @return string|boolean
     */
    public function updateKansoSettings($settings) 
    {
        # Get the current config
        $existingConfig = \Kanso\Kanso::getInstance()->Config;

        # Get the Environment
        $env = \Kanso\Kanso::getInstance()->Environment;

        # Declare variables we are going to use and do some santization
        $KansoPermalinks   = $this->createPermalinks($settings['permalinks']);
        $KansoThumbnails   = $this->createThumbnailsArray($settings['thumbnail-sizes']);
        $KansoCacheLife    = $this->validateCacheLife($settings['cache-life']);

        # Build the new configuration array
        $Kanso_Config = [
            "KANSO_THEME_NAME"       => str_replace("/", '', $settings['theme']),
            "KANSO_SITE_TITLE"       => $settings['site-title'],
            "KANSO_SITE_DESCRIPTION" => $settings['site-description'], 
            "KANSO_SITEMAP"          => $settings['sitemap-url'],
            "KANSO_PERMALINKS"       => $KansoPermalinks['KANSO_PERMALINKS'],
            "KANSO_PERMALINKS_ROUTE" => $KansoPermalinks['KANSO_PERMALINKS_ROUTE'],
            "KANSO_AUTHOR_SLUGS"     => $existingConfig['KANSO_AUTHOR_SLUGS'],
            "KANSO_POSTS_PER_PAGE"   => (int)$settings['posts-per-page'] < 1 ? 10 : $settings['posts-per-page'],
            "KANSO_ROUTE_TAGS"       => $settings['route-tags'] !== 'true' ? false : true,
            "KANSO_ROUTE_CATEGORIES" => $settings['route-categories'] !== 'true' ? false : true,
            "KANSO_ROUTE_AUTHORS"    => $settings['route-authors'] !== 'true' ? false : true,
            "KANSO_THUMBNAILS"       => $KansoThumbnails,
            "KANSO_IMG_QUALITY"      => (int)$settings['thumbnail-quality'],
            "KANSO_USE_CDN"          => $settings['use-CDN'] !== 'true' ? false : true,
            "KASNO_CDN_URL"          => $settings['CDN-url'],
            "KANSO_USE_CACHE"        => $settings['use-cache'] !== 'true' ? false : true,
            "KANSO_CACHE_LIFE"       => $KansoCacheLife,
            "KANSO_COMMENTS_OPEN"    => $settings['enable-comments'] !== 'true' ? false : true,
            "KANSO_STATIC_PAGES"     => $existingConfig['KANSO_STATIC_PAGES'],
        ];

        # Validate things that are required
        if ($Kanso_Config['KANSO_THEME_NAME'] === '' || !is_dir($env['KANSO_THEME_DIR'].'/'.$Kanso_Config['KANSO_THEME_NAME'])) return "theme_no_exist";
        if ($KansoPermalinks['KANSO_PERMALINKS'] === '' || $KansoPermalinks['KANSO_PERMALINKS_ROUTE'] === '' || strpos($KansoPermalinks['KANSO_PERMALINKS'], 'postname') === false) return "invalid_permalinks";
        if ($Kanso_Config['KANSO_IMG_QUALITY'] < 1 || $Kanso_Config['KANSO_IMG_QUALITY'] > 100) return "invalid_img_quality";
        if ($Kanso_Config['KANSO_USE_CDN'] === true && $Kanso_Config['KASNO_CDN_URL'] === '') return "invalid_cdn_url";
        if ($Kanso_Config['KANSO_USE_CACHE'] === true && !$KansoCacheLife) return 'invalid_cache_life';

        # Check if the user has changed the permalinks structure
        $changedPermalinks = $existingConfig['KANSO_PERMALINKS_ROUTE'] !== $KansoPermalinks['KANSO_PERMALINKS_ROUTE'];

        # Check if the user has changed use cache
        $changedCache = $existingConfig['KANSO_USE_CACHE'] !== $Kanso_Config['KANSO_USE_CACHE'];

        # Save the new config 
        $save = \Kanso\Kanso::getInstance()->setConfig($Kanso_Config);

        if ($save)  {

          # Refresh Kanso's Configureation
          \Kanso\Kanso::getInstance()->refreshConfig();

          # If permalinks were changed, we need to update every post in the DB
          if ($changedPermalinks) $this->updatePostPermalinks();

          # Clear the cache as well
          if ($changedCache) \Kanso\Kanso::getInstance()->Cache->clearCache();

          return 'valid';

        }

        return false;

    }

    /**
     * Restore Kanso to its factory settings
     *
     */
    public function restoreKanso() 
    {

        # Reinstall from defaults
        $installer = new \Kanso\Install\Installer();
        
        if ($installer->installKanso(true)) {

            # Return
            return 'valid';
        }

        return false;

    }

    /**
     * Update all the permalinks (slugs) in the database with
     * Kanso's configuration
     */
    private function updatePostPermalinks() 
    {
        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Get all the articles 
        $allPosts = $Query->getArticlesByIndex(null, null, null, ['tags', 'category', 'content', 'comments', 'author']);
       
        # Loop through the articles and update the slug and permalink
        if ($allPosts && !empty($allPosts)) {
            foreach ($allPosts as $post) {
                $newSlug = $this->titleToSlug($post['title'], $post['category']['slug'], $post['author'][0]['slug'], $post['created'], $post['type']);
                $postRow = self::$Kanso->Database->table('posts')->find($post['id']);
                $Query->UPDATE('posts')->SET(['slug', $newSlug])->WHERE('id', '=', $post['id'])->QUERY();
            }
        }
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

        # Static pages don't have a permalink structure
        if ($type === 'page') return \Kanso\Utility\Str::slugFilter($title);

        # Get the permalinks structure
        $format = self::$Kanso->Config['KANSO_PERMALINKS'];

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
            
            if (isset($dateMap[$key])) $slug .= date($dateMap[$key], $created).DIRECTORY_SEPARATOR;
            
            else if (isset($varMap[$key])) $slug .= $varMap[$key].DIRECTORY_SEPARATOR;
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
        return $thumbs;

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

    /**
     * Add a slug to Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be added
     */
    private function addAuthorSlug($slug)
    {
        # Get the slugs
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        # Add the slug
        $slugs[] = $slug;

        \Kanso\Kanso::getInstance()->setConfigPair('KANSO_AUTHOR_SLUGS', array_unique(array_values($slugs)));
    }

    /**
     * Remove a slug from Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be removed
     */
    private function removeAuthorSlug($slug)
    {
        # Get the config
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        # Remove the slug
        foreach ($slugs as $i => $configSlug) {
            if ($configSlug === $slug) unset($slugs[$i]);
        }

        \Kanso\Kanso::getInstance()->setConfigPair('KANSO_AUTHOR_SLUGS', array_values($slugs));
    }

}