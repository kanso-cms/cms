<?php

namespace kanso\cms\query;

use InvalidArgumentException;
use kanso\framework\database\query\Builder;
use kanso\cms\wrappers\providers\PostProvider;

/**
 * Query Parser
 *
 * This class is used by \kanso\query\Query to parse a string
 * query on the the database and return the results
 *
 */
class QueryParser
{
    /**
     * SQL query builder instance
     * 
     * @var \kanso\framework\database\query\Builder
     */ 
    private $SQL;

    /**
     * Post provider
     * 
     * @var \kanso\cms\wrappers\providers\PostProvider
     */ 
    private $postProvider;

    /**
     * The input query to parse
     *
     * @var string
     */
    private $queryStr;

    /**
     * Available database queries
     *
     * @var array
     */
    private $queryVars = 
    [
        'FROM'         => 'posts',
        'SELECT'       => [],
        'AND_WHERE'    => [],
        'OR_WHERE'     => [],
        'ORDER_BY'     => [],
        'LIMIT'        => [],
    ];

    /**
     * Defaults
     *
     * @var array
     */
    private $queryVarsDefaults;
    
    /**
     * Accepted logical operators
     *
     * @var array    
     */
    private static $acceptedOperators =
    [
        '=','!=', '>', '<', '>=', '<=', 'LIKE'
    ];
    
    /**
     * Accepted query keys > table key
     *
     * @var array    
     */
    private static $acceptedKeys = 
    [
        'post_id'        => 'id',
        'post_created'   => 'created',
        'post_modified'  => 'modified',
        'post_status'    => 'status',
        'post_type'      => 'type',
        'post_slug'      => 'slug',
        'post_title'     => 'title',
        'post_excerpt'   => 'excerpt',
        'post_thumbnail' => 'thumbnail',

        'tag_id'         => 'tags_to_posts.id',
        'tag_name'       => 'tags.name',
        'tag_slug'       => 'tags.slug',

        'category_id'    => 'categories_to_posts.id',
        'category_name'  => 'categories.name',
        'category_slug'  => 'categories.slug',

        'author_id'       => 'users.id',
        'author_username' => 'users.username',
        'author_email'    => 'users.email',
        'author_name'     => 'users.name',
        'author_slug'     => 'users.slug',
        'author_facebook' => 'users.facebook',
        'author_twitter'  => 'users.twitter',
        'author_gplus'    => 'users.gplus',
        'author_thumbnail'=> 'users.thumbnail',
    ];

    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\database\query\Builder    $SQL          SQL query builder
     * @param  \kanso\cms\wrappers\providers\PostProvider $postProvider Post provider instance
     */
    public function __construct(Builder $SQL, postProvider $postProvider)
    {
        $this->SQL = $SQL;

        $this->postProvider = $postProvider;

        $this->queryVarsDefaults = $this->queryVars;
    }

    /**
     * Parse a query and return the posts
     *
     * @access public
     * @param  $queryStr The query string to parse
     * @return array
     */
    public function parseQuery(string $queryStr): array
    {
        # Set the query string
        $this->queryStr = $queryStr;

        if (empty($this->queryStr))
        {
            return [];
        }

        # Reset internals
        $this->queryVars = $this->queryVarsDefaults;

        # Parse the query and execute the chain
        if ($this->parse())
        {
            return $this->executeQuery();
        }

        return [];
    }

    /**
     * Parse the provided query string into an array of callable functions
     *
     * @access private
     * @return bool
     * @throws InvalidArgumentException if the provided query is invalid, empty, or cannot be parsed
     */
    private function parse(): bool
    {
        $defaults =
        [
            'FROM'         => 'posts',
            'SELECT'       => [],
            'AND_WHERE'    => [],
            'OR_WHERE'     => [],
            'ORDER_BY'     => [],
            'LIMIT'        => [],
        ];

        # Split the string into query statements
        $statements = array_map('trim', explode(':', trim(trim($this->queryStr, ':'))));

        # Split statements into queries and methods
        $queries = [];
        
        $methods = [];

        foreach ($statements as $query)
        {
            # Split the string query into an array by any operator
            $query = array_filter(array_map('trim', preg_split("/(\>\=|\<\=|\<|\>|\!\=|\=|\&\&|\|\|)/", $query, NULL, PREG_SPLIT_DELIM_CAPTURE / PREG_SPLIT_NO_EMPTY)));

            # Check if the query is using regex
            $hasRegex = false;
            $newQuery = [];
            foreach ($query as $qur)
            {
                if (strpos($qur, 'LIKE') !== false)
                {
                    $hasRegex = true;
                    $rgxQuery = array_filter(array_map('trim', preg_split("/(LIKE)/", $qur, NULL, PREG_SPLIT_DELIM_CAPTURE / PREG_SPLIT_NO_EMPTY)));
                    $newQuery = array_merge($newQuery, $rgxQuery);
                }
                else
                {
                    $newQuery[] = $qur;
                }
            }
            if ($hasRegex)
            {
                $query = $newQuery;
            }

            # if a query has less than 3 values it is invalid
            if (count($query) < 3)
            {
                throw new InvalidArgumentException('Invalid query supplied QueryParser. The supplied query "'.$this->queryStr.'" is invalid.');
            }

            # if the query starts with keymap it is an andWhere()/orWhere() function
            # or group of functions
            if (isset(self::$acceptedKeys[$query[0]]) && !count($query) % 2 == 0)
            {
                $queries[] = $query;
            }

            # if the query starts with an accepted method it is a database method
            # or group of functions
            else if (in_array($query[0], ['orderBy', 'limit']))
            {
                $methods[] = $query;
            }
        }

        # Throw exception if queries and methods are empty
        if (empty($queries) && empty($methods))
        {
            throw new InvalidArgumentException('Invalid query supplied QueryParser. The supplied query "'.$this->queryStr.'" is invalid.');
        }

        # Loop through queries and append to chain
        foreach ($queries as $queryGroup)
        {
            # Throw exception if queries were invalid
            if (!$this->appendQueries($queryGroup))
            {
                throw new InvalidArgumentException('Invalid query supplied to QueryParser. The supplied query "'.$this->queryStr.'" is invalid.');
            }
        }

        # Loop through methods and append to chain
        foreach ($methods as $method)
        {
            $this->appendMethods($method);
        }

        # Throw exception if queries and methods are empty
        if ($this->queryVars === $defaults)
        {
            throw new InvalidArgumentException('Invalid query supplied QueryParser. The supplied query "'.$this->queryStr.'" is invalid.');
        }

        return true;
    }

    /**
     * Execute the current query
     * 
     * @access private
     * @return array
     */
    private function executeQuery(): array
    {
        $this->SQL->SELECT("posts.id");

        $this->SQL->FROM($this->queryVars['FROM']);

        if (!empty($this->queryVars['AND_WHERE']))
        {
            foreach ($this->queryVars['AND_WHERE'] as $condition)
            {
                if ($condition['op'] === 'LIKE')
                {
                    $condition['val'] = '%'.str_replace('%', '', $condition['val']).'%';
                }
                
                $this->SQL->AND_WHERE($condition['field'], $condition['op'], $condition['val']);
            }
        }

        if (!empty($this->queryVars['OR_WHERE']))
        {
            foreach ($this->queryVars['OR_WHERE'] as $condition)
            {
                if ($condition['op'] === 'LIKE')
                {
                    $condition['val'] = '%'.str_replace('%', '', $condition['val']).'%';
                }

                $this->SQL->OR_WHERE($condition['field'], $condition['op'], $condition['val']);
            }
        }

        $this->SQL->LEFT_JOIN_ON('users', 'users.id = posts.author_id');

        $this->SQL->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id');

        $this->SQL->LEFT_JOIN_ON('categories_to_posts', 'posts.id = categories_to_posts.post_id');

        $this->SQL->LEFT_JOIN_ON('categories', 'categories.id = categories_to_posts.category_id');

        $this->SQL->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');

        $this->SQL->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');

        $this->SQL->GROUP_BY('posts.id');

        if (!empty($this->queryVars['ORDER_BY']))
        {
            $this->SQL->ORDER_BY($this->queryVars['ORDER_BY'][0], $this->queryVars['ORDER_BY'][1]);
        }
        
        if (!empty($this->queryVars['LIMIT']))
        {
            if (isset($this->queryVars['LIMIT'][1]))
            {
                $this->SQL->LIMIT($this->queryVars['LIMIT'][0], $this->queryVars['LIMIT'][1]);
            }
            else
            {
                $this->SQL->LIMIT($this->queryVars['LIMIT'][0]);
            }
        }

        $articles = $this->SQL->FIND_ALL();

        $aticleObjs = [];
        
        if (!empty($articles))
        {
            foreach ($articles as $row)
            {
                $aticleObjs[] = $this->postProvider->byId($row['id']);
            }
        }

        return $aticleObjs;
    }

    /**
     * Returns all the post id's from the query
     * 
     * @access public
     * @param  string  $queryStr Query string to parse
     * @return array
     */
    public function countQuery(string $queryStr): array
    {        
        # Set the query string
        $this->queryStr = $queryStr;

        if (empty($this->queryStr))
        {
            return [];
        }

        # Reset internals
        $this->queryVars = $this->queryVarsDefaults;

        # Parse the query and execute the chain
        $this->parse();

        $this->SQL->SELECT("posts.id");

        $this->SQL->FROM($this->queryVars['FROM']);

        if (!empty($this->queryVars['AND_WHERE']))
        {
            foreach ($this->queryVars['AND_WHERE'] as $condition)
            {
                if ($condition['op'] === 'LIKE')
                {
                    $condition['val'] = '%'.str_replace('%', '', $condition['val']).'%';
                }
                
                $this->SQL->AND_WHERE($condition['field'], $condition['op'], $condition['val']);
            }
        }

        if (!empty($this->queryVars['OR_WHERE']))
        {
            foreach ($this->queryVars['OR_WHERE'] as $condition)
            {
                if ($condition['op'] === 'LIKE')
                {
                    $condition['val'] = '%'.str_replace('%', '', $condition['val']).'%';
                }

                $this->SQL->OR_WHERE($condition['field'], $condition['op'], $condition['val']);
            }
        }

        $this->SQL->LEFT_JOIN_ON('users', 'users.id = posts.author_id');

        $this->SQL->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id');

        $this->SQL->LEFT_JOIN_ON('categories_to_posts', 'posts.id = categories_to_posts.post_id');

        $this->SQL->LEFT_JOIN_ON('categories', 'categories.id = categories_to_posts.category_id');

        $this->SQL->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');

        $this->SQL->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');

        $this->SQL->GROUP_BY('posts.id');

        if (!empty($this->queryVars['ORDER_BY']))
        {
            $this->SQL->ORDER_BY($this->queryVars['ORDER_BY'][0], $this->queryVars['ORDER_BY'][1]);
        }

        return $this->SQL->FIND_ALL();
    }

    /**
     * Append a a group of queries to the methods
     *
     * @access private
     */
    private function appendMethods($method)
    {
        $call = $method[0];
        $op   = '=';
        $val  = $method[2];

        # orderBy and limit need in
        if ($call === 'limit')
        {
            $limit = array_map('intval', array_map('trim', explode(',', $val)));
            $this->queryVars['LIMIT'] = array_map('intval', array_map('trim', explode(',', $val)));
        }
        else if ($call === 'orderBy')
        {
            if (strpos(strtolower($val),',') !== false )
            {
                $sort  = array_map('trim', explode(',', $val));
                $order = (strpos(strtolower($sort[1]),'desc') !== false ? 'DESC' : 'ASC');
                $key   = isset(self::$acceptedKeys[$sort[0]]);
                
                if ($key)
                {
                    $this->queryVars['ORDER_BY'] = [self::$acceptedKeys[$sort[0]], $order];
                }
            }
        }
    }

    /**
     * Validates a query group
     *
     * @access private
     * @return bool 
     */
    private function appendQueries($queryGroup): bool
    {
        $groupFuncs = [];

        foreach ($queryGroup as $i => $value)
        {

            $groupCount = count($queryGroup);

            # Catch values with multiple query groups
            $isfunc = $value === '||' || $value === '&&';
            $isfunc = $isfunc && ($groupCount > 3 && (($i+1) % 4) == 0);
            $func   = $isfunc && $value === '||' ? 'OR_WHERE' : 'AND_WHERE';
            $op     = in_array($value, self::$acceptedOperators) ? $value : false;
            $key    = !$op && !$isfunc && isset(self::$acceptedKeys[$value]) ? $value : false;
            $val    = !$op && !$isfunc && !$key ? $value : false;

            if ($isfunc)
            {
                $groupFuncs[]['call'] = $func;
            }
            else if (!empty($groupFuncs) && $op)
            {
                $groupFuncs[count($groupFuncs)-1]['op'] = $op;
            }
            else if (!empty($groupFuncs) && $key)
            {
                $groupFuncs[count($groupFuncs)-1]['field'] = $key;
            }
            else if (!empty($groupFuncs) && $val)
            {
                $groupFuncs[count($groupFuncs)-1]['val'] = $val;
            }

            # Catch values with a single query group
            else
            {
                $op  = in_array($value, self::$acceptedOperators) ? $value : false;
                $key = !$op && isset(self::$acceptedKeys[$value]) ? $value : false;
                $val = !$op && !$key ? $value : false;
                $groupFuncs[]['call'] = $func;
                
                if ($op)
                {
                    $groupFuncs[count($groupFuncs)-1]['op'] = $op;
                }
                else if ($key)
                {
                    $groupFuncs[count($groupFuncs)-1]['field'] = $key;
                }
                else if ($val)
                {
                    $groupFuncs[count($groupFuncs)-1]['val'] = $val;
                }
            }
        }

        # Validate found queries
        foreach ($groupFuncs as $i => $group)
        {
            # Methods must have 4 values, key, op, field, value
            if (count($group) !== 4) return false;

            # Methods must be callable
            if (!isset($group['call'])) return false;
            if (!isset($this->queryVars[$group['call']])) return false;

            # Remove the caller function name
            $key = $group['call'];
            unset($group['call']);

            # Convert the key
            $group['field'] = self::$acceptedKeys[$group['field']];

            # Convert numeric values to integers
            if (is_numeric($group['val'])) $group['val'] = (int)$group['val'];

            # Append the function to the chain
            $this->queryVars[$key][] = $group;
        }

        # Be sure that Group funcs are not empty
        return !empty($groupFuncs);
    }
}