<?php

namespace Kanso\Config;

/**
 * Settings
 * 
 */
class Validator 
{

    private $defaults;
    
    /**
     * Constructor
     *
     * @param array    $defaults 
     *
     */
    public function __construct($defaults)
    {
        $this->defaults = $defaults;
    }

    /********************************************************************************
    * VALIDATION METHODS
    *******************************************************************************/

    public function validate($data)
    {
        # Validate all the keys are set
        foreach ($this->defaults as $key => $value) {
            if (!array_key_exists($key, $data)) {
                if ($key === 'KANSO_THEME_NAME')  return \Kanso\Config\Settings::INVALID_THEME;
                if ($key === 'KANSO_PERMALINKS')  return \Kanso\Config\Settings::INVALID_PERMALINKS;
                if ($key === 'KANSO_IMG_QUALITY') return \Kanso\Config\Settings::INVALID_IMG_QUALITY;
                if ($key === 'KANSO_USE_CDN')     return \Kanso\Config\Settings::INVALID_CDN_URL;
                if ($key === 'KANSO_USE_CACHE')   return \Kanso\Config\Settings::INVALID_CACHE_LIFE;
                return \Kanso\Config\Settings::UNKNOWN_ERROR;
            }
        }

        # Validate the permalinks
        if (!$this->validatePermalinks($data['KANSO_PERMALINKS'])) {
            return \Kanso\Config\Settings::INVALID_PERMALINKS;
        }

        # Validate the thumbnail sizes
        if (!$this->validateThumbnails($data['KANSO_THUMBNAILS'])) {
            return \Kanso\Config\Settings::INVALID_THUMBNAILS;
        }

        # Validate the cache lifetime
        if ($data['KANSO_USE_CACHE'] === true) {
            $cacheLife = $this->validateCacheLife($data['KANSO_CACHE_LIFE']);
            if (!$cacheLife) {
                return \Kanso\Config\Settings::INVALID_CACHE_LIFE;
            }
        }

        # Validate the theme
        $themDir = \Kanso\Kanso::getInstance()->Environment['KANSO_THEMES_DIR'].DIRECTORY_SEPARATOR.$data['KANSO_THEME_NAME'];
        if ($data['KANSO_THEME_NAME'] === '' || !is_dir($themDir)) {
            return \Kanso\Config\Settings::INVALID_THEME;
        }

        # Validate the image quality
        if (!is_integer($data['KANSO_IMG_QUALITY'])) return \Kanso\Config\Settings::INVALID_IMG_QUALITY;
        if ($data['KANSO_IMG_QUALITY'] < 1 || $data['KANSO_IMG_QUALITY'] > 100) {
            return \Kanso\Config\Settings::INVALID_IMG_QUALITY;
        }

        # Validate the CDN url
        if ($data['KANSO_USE_CDN'] === true) {
            if (!filter_var($data['KASNO_CDN_URL'], FILTER_VALIDATE_URL)) {
                return \Kanso\Config\Settings::INVALID_CDN_URL;
            }
        }

        # Validate the posts per page
        if (!is_integer($data['KANSO_POSTS_PER_PAGE'])) {
            return \Kanso\Config\Settings::INVALID_POSTS_PER_PAGE;
        }

        return true;
    }

    /**
     * Validate a permalink value
     *
     * @param  string    $url    The url to be converted
     * @return boolean
     *
     */
    private function validatePermalinks($url) 
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

        if ($permaLink === '' || $route === '' || strpos($permaLink, 'postname') === false) {
            return false;
        }
        return true;
    }

    /**
     * Validate thumbnail sizes
     *
     * @param  string   $thumbnails    Comma separated list of thumbnail sizes or array or thumbnail sizes
     * @return boolean
    */
    private function validateThumbnails($thumbnails) 
    {
        
        if (is_string($thumbnails) && $thumbnails !== '') {
            $thumbs     = [];
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
            return count($thumbs) === 3;
        }
        return true;
    }

    /**
     * Validate cache lifetime
     *
     * @param  string       $cacheLife  A cache life - e.g '3 hours'
     * @return bool
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

        return true;
    }

    /********************************************************************************
    * FILTER METHODS
    *******************************************************************************/
    /**
     * Filter and sanitize the configuration to unsure Kanso will run
     * 
     * @return array
     */
    public function filter($data)
    {
        $config = $data;
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
        $config['KANSO_POSTS_PER_PAGE']     = intval($config['KANSO_POSTS_PER_PAGE']);
        $config['KANSO_ROUTE_TAGS']         = \Kanso\Utility\Str::bool( $config['KANSO_ROUTE_TAGS']);
        $config['KANSO_ROUTE_CATEGORIES']   = \Kanso\Utility\Str::bool($config['KANSO_ROUTE_CATEGORIES']);
        $config['KANSO_ROUTE_AUTHORS']      = \Kanso\Utility\Str::bool($config['KANSO_ROUTE_AUTHORS']);
        $config['KANSO_THUMBNAILS']         = $this->filterThumbnails($config['KANSO_THUMBNAILS']);
        $config['KANSO_IMG_QUALITY']        = (int) $config['KANSO_IMG_QUALITY'];
        $config['KANSO_USE_CDN']            = \Kanso\Utility\Str::bool($config['KANSO_USE_CDN']);
        $config['KASNO_CDN_URL']            = filter_var($config['KASNO_CDN_URL'], FILTER_SANITIZE_STRING);
        $config['KANSO_USE_CACHE']          = \Kanso\Utility\Str::bool( $config['KANSO_USE_CACHE']);
        $config['KANSO_CACHE_LIFE']         = $this->filterCacheLife($config['KANSO_CACHE_LIFE']);
        $config['KANSO_COMMENTS_OPEN']      = \Kanso\Utility\Str::bool($config['KANSO_COMMENTS_OPEN']);
        $config['KANSO_OWNER_USERNAME']     = filter_var($config['KANSO_OWNER_USERNAME'], FILTER_SANITIZE_STRING);
        $config['KANSO_OWNER_EMAIL']        = filter_var($config['KANSO_OWNER_EMAIL'], FILTER_SANITIZE_STRING);
        $config['KANSO_OWNER_PASSWORD']     = filter_var($config['KANSO_OWNER_PASSWORD'], FILTER_SANITIZE_STRING);
        $config['KANSO_STATIC_PAGES']       = $config['KANSO_STATIC_PAGES'];
        $config['KANSO_AUTHOR_SLUGS']       = $config['KANSO_AUTHOR_SLUGS'];

        # Fiter and sanitize the permalinks
        $permalinks = $this->filterPermalinks($config['KANSO_PERMALINKS']);
        $config['KANSO_PERMALINKS_ROUTE'] = $permalinks['KANSO_PERMALINKS_ROUTE'];
        $config['KANSO_PERMALINKS']       = $permalinks['KANSO_PERMALINKS'];

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
     * Filter the permalinks
     *
     * @param  string   $url    The url to be converted
     * @return array            Array with the the actual link and the route
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
            'KANSO_PERMALINKS' => $permaLink,
            'KANSO_PERMALINKS_ROUTE' => $route,
        ];
    }

    /**
     * Convert a list of thumbnail sizes into an array
     *
     * @param  string   $thumbnails    Comma separated list of thumbnail sizes or array of thumbnail sizes
     * @return array                   Array of thumbnail sizes
    */
    private function filterThumbnails($thumbnails) 
    {
        $thumbs = [];
        if (is_string($thumbnails)) {
            $thumbnails = array_map('trim', explode(',', $thumbnails));
            foreach ($thumbnails as $i => $values) {
                if ($i > 2) break;
                $values = preg_replace("/[^\d+ ]/", "", $values);
                $values = array_map('trim', explode(' ', $values));
                if (count($values) === 1) {
                    $thumbs[$i] = intval($values[0]);
                }
                else if (isset($values[1])) {
                    $thumbs[$i] = [intval($values[0]), intval($values[1])];
                }
            }
        }
        else if (is_array($thumbnails)) {
            foreach ($thumbnails as $i => $thumb) {
                if ($i > 2) break;
                if (is_array($thumb)) {
                    if (count($thumb) === 2) {
                        $thumbs[$i] = [intval($thumb[0]), intval($thumb[1])];
                    }
                    else {
                        $thumbs[$i] = intval($thumb[0]);
                    }
                }
                else {
                    $thumbs[$i] = intval($thumb);
                }
            }
        }

        return $thumbs;
    }

    /**
     * Filter the cache life
     *
     * @param  string       $cacheLife  A cache life - e.g '3 hours'
     * @return string|bool
     */
    private function filterCacheLife($cacheLife) 
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