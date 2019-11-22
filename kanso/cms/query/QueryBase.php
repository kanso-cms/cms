<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query;

use InvalidArgumentException;
use kanso\cms\query\helpers\Attachment;
use kanso\cms\query\helpers\Author;
use kanso\cms\query\helpers\Cache;
use kanso\cms\query\helpers\Category;
use kanso\cms\query\helpers\Comment;
use kanso\cms\query\helpers\Helper;
use kanso\cms\query\helpers\Meta;
use kanso\cms\query\helpers\Pagination;
use kanso\cms\query\helpers\Parser;
use kanso\cms\query\helpers\Post;
use kanso\cms\query\helpers\PostIteration;
use kanso\cms\query\helpers\Scripts;
use kanso\cms\query\helpers\Search;
use kanso\cms\query\helpers\Tag;
use kanso\cms\query\helpers\Templates;
use kanso\cms\query\helpers\Urls;
use kanso\cms\query\helpers\Validation;
use kanso\framework\ioc\Container;

/**
 * CMS Query object.
 *
 * @author Joe J. Howard
 */
abstract class QueryBase
{
    /**
     * The page request type.
     *
     * @var string|null
     */
    public $requestType = 'custom';

    /**
     * The string-query to use on the database.
     *
     * @var string|null
     */
    public $queryStr;

    /**
     * Current page request if it exists.
     *
     * @var int
     */
    public $pageIndex = 0;

    /**
     * Current post index of paginated array of posts.
     *
     * @var int
     */
    public $postIndex = -1;

    /**
     * Current post count.
     *
     * @var int
     */
    public $postCount = 0;

    /**
     * Array of posts from query result.
     *
     * @var array
     */
    public $posts = [];

    /**
     * The current post.
     *
     * @var \kanso\cms\wrappers\Post|null
     */
    public $post = null;

    /**
     * Current taxonomy slug if applicable (e.g tag, category, author).
     *
     * @var string|null
     */
    public $taxonomySlug;

    /**
     * Current attachment URL: if applicable (e.g foo.com/app/public/uploads/my-image_large.png).
     *
     * @var string|null
     */
    public $attachmentURL;

    /**
     * Current attachment size: if applicable (image_large).
     *
     * @var string|null
     */
    public $attachmentSize;

    /**
     * Search term if applicable.
     *
     * @var string|null
     */
    public $searchQuery;

    /**
     * Header scripts.
     *
     * @var array
     */
    public $headerScripts = [];

    /**
     * Header scripts.
     *
     * @var array
     */
    public $headerStyles = [];

    /**
     * Footer scripts.
     *
     * @var array
     */
    public $footerScripts = [];

    /**
     * IoC container instance.
     *
     * @var \kanso\framework\ioc\Container
     */
    protected $container;

    /**
     * Helper classes.
     *
     * @var array
     */
    protected $helperClasses =
    [
        'attachment'    => Attachment::class,
        'author'        => Author::class,
        'cache'         => Cache::class,
        'category'      => Category::class,
        'comment'       => Comment::class,
        'meta'          => Meta::class,
        'pagination'    => Pagination::class,
        'post'          => Post::class,
        'postIteration' => PostIteration::class,
        'search'        => Search::class,
        'tag'           => Tag::class,
        'templates'     => Templates::class,
        'scripts'       => Scripts::class,
        'urls'          => Urls::class,
        'validation'    => Validation::class,
        'parser'        => Parser::class,
    ];

    /**
     * Helper classes.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Constructor.
     *
     * @param \kanso\framework\ioc\Container $container IoC container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->pageIndex = $this->fetchPageIndex();
    }

    /**
     * Create and return a new Query object.
     *
     * @param  string                 $queryStr Query to filter posts
     * @return \kanso\cms\query\Query
     */
    public function create(string $queryStr = ''): Query
    {
        $instance = new Query($this->container);

        $instance->applyQuery($queryStr);

        return $instance;
    }

    /**
     * Retrieves and returns a helper class by name.
     *
     * @param  string                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $name Name of helper class
     * @throws \InvalidArgumentException                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       If class does not exist
     * @return \kanso\cms\query\helpers\Attachment|\kanso\cms\query\helpers\Author|\kanso\cms\query\helpers\Cache|\kanso\cms\query\helpers\Category|\kanso\cms\query\helpers\Comment|\kanso\cms\query\helpers\Helper|\kanso\cms\query\helpers\Meta|\kanso\cms\query\helpers\Pagination|\kanso\cms\query\helpers\Parser|\kanso\cms\query\helpers\Post|\kanso\cms\query\helpers\PostIteration|\kanso\cms\query\helpers\Search|\kanso\cms\query\helpers\Tag|\kanso\cms\query\helpers\Templates|\kanso\cms\query\helpers\Urls|\kanso\cms\query\helpers\Validation|\kanso\cms\query\helpers\Scripts
     */
    public function helper(string $name): Helper
    {
        if (isset($this->helpers[$name]))
        {
            return $this->helpers[$name];
        }

        foreach ($this->helperClasses as $key => $class)
        {
            if ($key === $name)
            {
                $class = new $class($this->container, $this);

                $this->helpers[$key] = $class;

                return $class;
            }
        }

        throw new InvalidArgumentException('Invalid helper class. Class "' . $name . '" does not exist.');
    }

    /**
     * Apply a query for a custom string.
     *
     * @param string $queryStr    Query string to parse
     * @param string $requestType Request type (optional) (default 'custom')
     */
    public function applyQuery(string $queryStr, $requestType = 'custom'): void
    {
        $this->reset();

        $this->queryStr = trim($queryStr);

        $this->posts = $this->helper('parser')->parseQuery($this->queryStr);

        $this->postCount = count($this->posts);

        $this->requestType = $requestType;

        if (isset($this->posts[0]))
        {
            $this->post = $this->posts[0];
        }
    }

    /**
     * Reset the internal properties to default.
     */
    public function reset(): void
    {
        $this->pageIndex    = 0;
        $this->postIndex    = -1;
        $this->postCount    = 0;
        $this->posts        = [];
        $this->requestType  = null;
        $this->queryStr     = null;
        $this->post         = null;
        $this->taxonomySlug = null;
        $this->searchQuery  = null;
        $this->pageIndex    = $this->fetchPageIndex();
    }

    /**
     * Fetch and set the currently requested page.
     *
     * @return int
     */
    private function fetchPageIndex(): int
    {
        $pageIndex = $this->container->Request->fetch('page');

        return $pageIndex === 1 || $pageIndex === 0 ? 0 : $pageIndex-1;
    }
}
