<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

/**
 * CMS Query properties
 *
 * @author Joe J. Howard
 */
trait Properties
{
	/**
     * The page request type
     *
     * @var string
     */
    public $requestType = 'custom';

    /**
     * The string-query to use on the database
     *
     * @var string    
     */
    public $queryStr;

    /**
     * Current page request if it exists
     *
     * @var int    
     */
    public $pageIndex = 0;

    /**
     * Current post index of paginated array of posts
     *
     * @var int   
     */
    public $postIndex = -1;

    /**
     * Current post count
     *
     * @var int    
     */
    public $postCount = 0;

    /**
     * Array of posts from query result
     *
     * @var array   
     */
    public $posts = [];

    /**
     * The current post
     *
     * @var array    
     */
    public $post = null;

    /**
     * Current taxonomy slug if applicable (e.g tag, category, author)
     *
     * @var string    
     */
    private $taxonomySlug;

    /**
     * Current attachment URL: if applicable (e.g foo.com/app/public/uploads/my-image_large.png)
     *
     * @var string    
     */
    private $attachmentURL;

    /**
     * Current attachment size: if applicable (image_large)
     *
     * @var string    
     */
    private $attachmentSize;

    /**
     * Search term if applicable
     *
     * @var string    
     */
    private $searchQuery;

    /**
     * Array of previously called methods and results
     *
     * @var array     
     */
    private $methodCache = [];

    /**
     * SQL query builder instance
     * 
     * @var \kanso\framework\database\query\Builder
     */ 
    private $SQL;

    /**
     * Config 
     * 
     * @var \kanso\cms\query\QueryParser
     */
    private $queryParser;

    /**
     * Method cache 
     * 
     * @var \kanso\cms\query\Cache
     */
    private $cache;
}
