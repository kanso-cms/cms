<?php

namespace Kanso\Cache;

/**
 * Cache
 *
 * This class is used to save/load/validate internal HTML page caching.
 *
 * The cache class will save HTML output to files. When a request is made
 * that is cached, the HTML file is loaded directly into the HTTP resonse body
 * skipping the processing of any variables.
 *
 * This can greatly improve response times on sites with heavy traffic.
 */
Class Cache 
{

    /**
     * @var bool      Is caching enabled?
     */
    private $useCache;

    /**
     * @var string    The name of the file being loaded/saved
     */
    private $cacheFile;

    /**
     * @var int       Unix timestamp of cache lifetime
     */
    private $cacheLife;

    /**
     * @var bool      If this is an exception?
     */
    private $isException;

    /**
     * @var bool      If this is an exception or not
     */
    private $isCahced;

    /**
     * @var string    The current page type
     */
    private $pageType;

    /**
     * Constructor
     *
     * @param  array    $config            \Kanso->Config
     * @param  array    $requestMethod     \Kanso->Request->getMethod()
     *
     */
    public function __construct()
    {

        # Load Kanso's Config
        $Config = \Kanso\Kanso::getInstance()->Config();

        # Load Kanso's Enviroment
        $Environment = \Kanso\Kanso::getInstance()->Environment();

        # Declare if caching is enabled
        $this->useCache = (bool)$Config['KANSO_USE_CACHE'];

        # Caching is only enabled for GET requests
        $this->useCache = \Kanso\Kanso::getInstance()->Request()->getMethod() === 'GET' ? $this->useCache : false;

        # Declare the cache lifetime
        $this->cacheLife = strtotime('-'.$Config['KANSO_CACHE_LIFE']);

        # Declare the current cache file
        $url  = rtrim($Environment['REQUEST_URI'], '/');
        $name = substr($url, strrpos($url, '/') + 1);
        
        # Store the name of the cache file
        $this->cacheFile =  $Environment['KANSO_DIR'].DIRECTORY_SEPARATOR.'Cache'.DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR.\Kanso\Utility\Str::slugFilter($name).'.html';
    }


    /********************************************************************************
    * PUBLIC ACCESS
    *******************************************************************************/
    public function put($html, $slug = NULL)
    {
        file_put_contents($this->cacheKey($slug), $html);
    }

    public function get($slug = NULL)
    {
        if ($this->has($slug)) {
            return file_get_contents($this->cacheKey($slug));
        }
        return NULL;
    }

    public function has($slug = NULL)
    {
        return file_exists($this->cacheKey($slug));
    }

    public function remove($slug = NULL)
    {
        return unlink($this->cacheKey($slug));
    }

    public function time($slug = NULL)
    {
        if ($this->has($slug)) {
            return filemtime($this->cacheKey($slug));
        }
        return NULL;
    }

    public function expired($life, $slug = NULL)
    {
        if (!$this->has($slug)) return false;  
        return $this->time($slug) < strtotime('-'.$life);
    }

    public function clear()
    {
        $files = glob($this->cacheDir().DIRECTORY_SEPARATOR.'*');
        foreach ($files as $file) { 
            if(is_file($file)) if (!unlink($file)) return false;
        }
        return true;
    }

    private function cacheKey($slug = NULL)
    {
        if (!$slug) {
            $key = \Kanso\Kanso::getInstance()->Environment['REQUEST_URI'];
        }
        else {
            $key = \Kanso\Kanso::getInstance()->Environment['HTTP_HOST'].'/'.$slug;
        }
        $key = \Kanso\Utility\Str::slugFilter($key);
        return $this->cacheDir().DIRECTORY_SEPARATOR.$key.'.html';
    }

    private function cacheDir()
    {
        return \Kanso\Kanso::getInstance()->Environment['KANSO_DIR'].DIRECTORY_SEPARATOR.'Cache'.DIRECTORY_SEPARATOR.'Library';
    }


    /********************************************************************************
    * PRIVATE ACCESS - KANSO APPLICATION ONLY
    *******************************************************************************/

    /**
     * Is the current request a cache exception?
     *
     * @return  bool
     */
    public function isException()
    {
        if ($this->pageType === 'single' && $this->useCache === true) {
            $this->isException = false;
            return false;
        }
        $this->isException = true;
        return true;
    }

    /**
     * Get the pageType
     *
     * @return  string
     */
    public function pageType()
    {
       return $this->pageType;
    }

    /**
     * Set the pageType
     *
     * @param string    $type
     *
     */
    public function setPageType($type)
    {
       return $this->pageType = $type;
    }
    
    /**
     * Is there a cached version of the current request?
     *
     * This will check for a file for the current request. If the file
     * was created/modified after the cache life expires, the file is deleted
     * and false is returned
     *
     * @return bool
     */
    public function isCahced() 
    {
        if (is_bool($this->useCache)) return $this->useCache;
        if ($this->isException() || !$this->useCache) {
            $this->isCahced = false;
        }
        else if (file_exists($this->cacheFile) && is_file($this->cacheFile)) {
            $last_cached = filemtime($this->cacheFile);
            if ($last_cached < $this->cacheLife) {
                unlink($cached);
                $this->isCahced = false;
            }
            else {
                $this->isCahced = true;
            }
        }
        else {
            $this->isCahced = false;
        }
        return $this->isCahced;
    }

    /**
     * Set if the cage is cached
     *
     * @param boolean    $bool
     *
     */
    public function setIsCahced($bool) 
    {
        $this->isCahced = $bool;
    }
    
    /**
     * Load the cached file
     *
     * @return string|false
     */
    public function LoadFromCache() 
    {
        if (file_exists($this->cacheFile) && is_file($this->cacheFile)) return file_get_contents($this->cacheFile);
        return false;
    }

    /**
     * Save to cache
     *
     * @param  string        $HTML    The html content to save
     * @return bool
     */
    public function saveToCache($HTML) 
    {
        return file_put_contents($this->cacheFile, $HTML);
    }

    /**
     * Clear the entire cache or a single file
     *
     * @param  string    $url    A valid permalink wildcard (optional)
     * @return bool
     */
    public function clearCache($url = false) 
    {

        if ($url) {
            $name = substr($url, strrpos($url, '/') + 1);
            $name = \Kanso\Utility\Str::slugFilter(preg_replace("/\..+/", '', $name));
            $file = \Kanso\Kanso::getInstance()->Environment['KANSO_DIR'].DIRECTORY_SEPARATOR.'Cache'.DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR.$name.'.html';
            if (file_exists($file) && is_file($file)) return unlink($file);
        }
        else {
            $files = glob(\Kanso\Kanso::getInstance()->Environment['KANSO_DIR'].DIRECTORY_SEPARATOR.'Cache'.DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR.'*');
            foreach($files as $file) { 
                if(is_file($file)) if (!unlink($file)) return false;
            }
            return true;
        }
        return false;
    }
}