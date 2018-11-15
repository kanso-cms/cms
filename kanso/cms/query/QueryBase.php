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
use kanso\cms\query\helpers\Filter;
use kanso\cms\query\helpers\Helper;
use kanso\cms\query\helpers\Meta;
use kanso\cms\query\helpers\Pagination;
use kanso\cms\query\helpers\Parser;
use kanso\cms\query\helpers\Post;
use kanso\cms\query\helpers\PostIteration;
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
     * @var string
     */
    public $requestType = 'custom';

    /**
     * The string-query to use on the database.
     *
     * @var string
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
     * @var \kanso\cms\wrappers\Post
     */
    public $post = null;

    /**
     * Current taxonomy slug if applicable (e.g tag, category, author).
     *
     * @var string
     */
    public $taxonomySlug;

    /**
     * Current attachment URL: if applicable (e.g foo.com/app/public/uploads/my-image_large.png).
     *
     * @var string
     */
    public $attachmentURL;

    /**
     * Current attachment size: if applicable (image_large).
     *
     * @var string
     */
    public $attachmentSize;

    /**
     * Search term if applicable.
     *
     * @var string
     */
    public $searchQuery;

    /**
     * Helper classes.
     *
     * @var array
     */
    public $helperClasses =
    [
        'attachment'    => Attachment::class,
        'author'        => Author::class,
        'cache'         => Cache::class,
        'category'      => Category::class,
        'comment'       => Comment::class,
        'filter'        => Filter::class,
        'meta'          => Meta::class,
        'pagination'    => Pagination::class,
        'post'          => Post::class,
        'postIteration' => PostIteration::class,
        'search'        => Search::class,
        'tag'           => Tag::class,
        'templates'     => Templates::class,
        'urls'          => Urls::class,
        'validation'    => Validation::class,
        'parser'        => Parser::class,
    ];

    /**
     * Helper classes.
     *
     * @var array
     */
    public $helpers = [];

    /**
     * IoC container instance.
     *
     * @var \kanso\framework\ioc\Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\ioc\Container $container IoC container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->loadDependencies();

        $this->helper('filter')->fetchPageIndex();
    }

    /**
     * Create and return a new Query object.
     *
     * @access public
     * @param  string queryStr  Query to filter posts
     * @return \kanso\cms\query\Query
     */
    public function create(string $queryStr = ''): Query
    {
        $instance = clone $this;

        $instance->applyQuery($queryStr);

        return $instance;
    }

    /**
     * Retrieves and returns a helper class by name.
     *
     * @access public
     * @param  string                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         $name Name of helper class
     * @throws \InvalidArgumentException                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      If class does not exist
     * @return \kanso\cms\query\helpers\Attachment|\kanso\cms\query\helpers\Author|\kanso\cms\query\helpers\Cache|\kanso\cms\query\helpers\Category|\kanso\cms\query\helpers\Comment|\kanso\cms\query\helpers\Filter|\kanso\cms\query\helpers\Helper|\kanso\cms\query\helpers\Meta|\kanso\cms\query\helpers\Pagination|\kanso\cms\query\helpers\Parser|\kanso\cms\query\helpers\Post|\kanso\cms\query\helpers\PostIteration|\kanso\cms\query\helpers\Search|\kanso\cms\query\helpers\Tag|\kanso\cms\query\helpers\Templates|\kanso\cms\query\helpers\Urls|\kanso\cms\query\helpers\Validation
     */
    public function helper(string $name): Helper
    {
        if (isset($this->helpers[$name]))
        {
            return $this->helpers[$name];
        }

        throw new InvalidArgumentException('Invalid helper class. Class "' . $name . '" does not exist.');
    }

    /**
     * Loads dependencies.
     *
     * @access private
     */
    private function loadDependencies()
    {
        foreach ($this->helperClasses as $key => $class)
        {
            $class = new $class($this->container);

            $class->setParent($this);

            $this->helpers[$key] = $class;
        }
    }

    public abstract function applyQuery(string $queryStr);
}
