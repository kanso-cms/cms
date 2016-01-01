<?php

namespace Kanso\Admin\Utility;

/**
 * Admin Settings Manager
 *
 * This class has as a number of utility helper functions
 * for managing Kanso's settings and administrator settings from within the admin panel.
 *
 */
class settingsManager
{

    /**
     * @var \Kanso\Kanso
     */
    protected static $Kanso;

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
    public static function updateKansoSettings($settings) 
    {
        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Declare variables we are going to use and do some santization
        $KansoPermalinks   = self::createPermalinks($settings['permalinks']);
        $KansoThumbnails   = self::createThumbnailsArray($settings['thumbnail-sizes']);
        $KansoCacheLife    = self::validateCacheLife($settings['cache-life']);

        # Build the new configuration array
        $Kanso_Config = [
            "KANSO_THEME_NAME"       => str_replace("/", '', $settings['theme']),
            "KANSO_SITE_TITLE"       => $settings['site-title'],
            "KANSO_SITE_DESCRIPTION" => $settings['site-description'], 
            "KANSO_SITEMAP"          => $settings['sitemap-url'],
            "KANSO_PERMALINKS"       => $KansoPermalinks['KANSO_PERMALINKS'],
            "KANSO_PERMALINKS_ROUTE" => $KansoPermalinks['KANSO_PERMALINKS_ROUTE'],
            "KANSO_AUTHOR_SLUGS"     => self::$Kanso->Config['KANSO_AUTHOR_SLUGS'],
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
            "KANSO_STATIC_PAGES"     => self::$Kanso->Config['KANSO_STATIC_PAGES'],
        ];

        # Validate things that are required
        if ($Kanso_Config['KANSO_THEME_NAME'] === '' || !is_dir(self::$Kanso->Environment['KANSO_THEME_DIR'].'/'.$Kanso_Config['KANSO_THEME_NAME'])) return "theme_no_exist";
        if ($KansoPermalinks['KANSO_PERMALINKS'] === '' || $KansoPermalinks['KANSO_PERMALINKS_ROUTE'] === '' || strpos($KansoPermalinks['KANSO_PERMALINKS'], 'postname') === false) return "invalid_permalinks";
        if ($Kanso_Config['KANSO_IMG_QUALITY'] < 1 || $Kanso_Config['KANSO_IMG_QUALITY'] > 100) return "invalid_img_quality";
        if ($Kanso_Config['KANSO_USE_CDN'] === true && $Kanso_Config['KASNO_CDN_URL'] === '') return "invalid_cdn_url";
        if ($Kanso_Config['KANSO_USE_CACHE'] === true && !$KansoCacheLife) return 'invalid_cache_life';

        # Check if the user has changed the permalinks structure
        $changedPermalinks = self::$Kanso->Config['KANSO_PERMALINKS_ROUTE'] !== $KansoPermalinks['KANSO_PERMALINKS_ROUTE'];

        # Check if the user has changed use cache
        $changedCache = self::$Kanso->Config['KANSO_USE_CACHE'] !== $Kanso_Config['KANSO_USE_CACHE'];

        # Save the new config 
        $save = self::$Kanso->setConfig($Kanso_Config);

        if ($save)  {

          # Refresh Kanso's Configureation
          self::$Kanso->refreshConfig();

          # If permalinks were changed, we need to update every post in the DB
          if ($changedPermalinks) self::updatePostPermalinks();

          # Clear the cache as well
          if ($changedCache) self::$Kanso->Cache->clearCache();

          \Kanso\Events::fire('configChange', [$Kanso_Config]);

          return 'valid';

        }

        return false;

    }

    /**
     * Restore Kanso to its factory settings
     *
     */
    public static function restoreKanso() 
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

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
    private static function updatePostPermalinks() 
    {

        # Get all the articles 
        $allPosts = self::$Kanso->Database->table('posts')->leftJoin('categories')->leftJoin('tags')->leftJoin('authors')->findAll()->asArray();
        
        # Loop through the articles and update the slug and permalink
        if ($allPosts && !empty($allPosts)) {
            foreach ($allPosts as $post) {
                $newSlug = self::titleToSlug($post['title'], $post['categories'][0]['slug'], $post['authors'][0]['slug'], $post['created'], $post['type']);
                $postRow = self::$Kanso->Database->table('posts')->find($post['id']);
                $postRow->slug = $newSlug;
                $postRow->save();
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
    private static function titleToSlug($title, $categorySlug, $authorSlug, $created, $type) 
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
    private static function createPermalinks($url) 
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
    private static function createThumbnailsArray($thumbnails) 
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
     * Remove a slug from Kanso's static pages configuration
     *
     * @param  string    $slug
     */
    public static function removeFromStaticPages($slug) 
    {
        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        $slugs   = self::$Kanso->Config['KANSO_STATIC_PAGES'];
        if($key = array_search($slug, $slugs) !== false) unset($slugs[$key]);
        self::$Kanso->setConfigPair('KANSO_STATIC_PAGES', array_values(array_unique($slugs)));
    }

    /**
     * Remove a slug from Kanso's static pages configuration
     *
     * @param  string    $slug
     */
    public static function addToStaticPages($slug) 
    {
        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        $slugs   = self::$Kanso->Config['KANSO_STATIC_PAGES'];
        $slugs[] = $slug;
        self::$Kanso->setConfigPair('KANSO_STATIC_PAGES', array_values(array_unique($slugs)));
    }
}