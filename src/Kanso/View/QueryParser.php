<?php

namespace Kanso\View;

/**
 * Query Parser
 *
 * This class is used by \Kanso\View\Query to parse a string
 * query on the the database and return the results
 *
 */
class QueryParser {

    /**
     * @var    array    Default query methods
     */
    public $QueryVars = [
        'FROM'         => 'posts',
        'SELECT'       => [],
        'AND_WHERE'    => [],
        'OR_WHERE'     => [],
        'ORDER_BY'     => [],
        'LIMIT'        => [],
    ];

    /**
     * @var    string    The query to parse
     */
    private $QueryStr;

    /**
     * @var array    Accepted chain methods that can be called
     */
    private static $acceptedMethods  = ['orderBy', 'limit'];

    /**
     * @var array    Accepted operators that can be used within a query
     */
    private static $acceptedOperators = [ '=', '!=', '>', '<', '>=', '<=', 'LIKE' ];
    
    /**
     * @var array    Accepted query fields that can be used within a query
     */
    private static $acceptedKeys = [
        
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

        'category_id'    => 'categories.id',
        'category_name'  => 'categories.name',
        'category_slug'  => 'categories.slug',

        'author_id'       => 'authors.id',
        'author_username' => 'authors.username',
        'author_email'    => 'authors.email',
        'author_name'     => 'authors.name',
        'author_slug'     => 'authors.slug',
        'author_facebook' => 'authors.facebook',
        'author_twitter'  => 'authors.twitter',
        'author_gplus'    => 'authors.gplus',
        'author_thumbnail'=> 'authors.thumbnail',
    ];
    
    /**
     * Constructor
     * 
     * @param $QueryStr    The  query string to parse
     */
    public function __construct($QueryStr = '')
    {
        $this->QueryStr = $QueryStr;
    }

    /**
     * Parse the loaded query
     * 
     * @param $QueryStr    The  query string to parse (optional)
     * @return array
     */
    public function parseQuery($QueryStr = null)
    {

        if ($QueryStr) $this->QueryStr = $QueryStr;

        # If the query is empty or not provided return all the posts
        if (trim($this->QueryStr) === '' || !$this->QueryStr) return $this->queryAllPosts();

        # Parse the query and execute the chain
        if ($this->parse()) return $this->executeQuery();
    }

    /**
     * Load all the posts from the database
     * 
     * @return array
     */
    private function queryAllPosts()
    {
        return \Kanso\Kanso::getInstance()->Database()->Builder()->getArticlesByIndex(null, null, null, ['tags', 'category', 'author', 'comments']);
    }

    /**
     * Parse the current query
     * 
     * @return array
     */
    private function executeQuery() 
    {
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        $Query->SELECT("posts.*");

        $Query->FROM($this->QueryVars['FROM']);

        if (!empty($this->QueryVars['AND_WHERE'])) {
            foreach ($this->QueryVars['AND_WHERE'] as $condition) {
                if ($condition['op'] === 'LIKE') $condition['val'] = '%'.str_replace('%', '', $condition['val']).'%';
                $Query->AND_WHERE($condition['field'], $condition['op'], $condition['val']);
            }
        }
        if (!empty($this->QueryVars['OR_WHERE'])) {
            foreach ($this->QueryVars['OR_WHERE'] as $condition) {
                if ($condition['op'] === 'LIKE') $condition['val'] = '%'.str_replace('%', '', $condition['val']).'%';
                $Query->OR_WHERE($condition['field'], $condition['op'], $condition['val']);
            }
        }

        $Query->LEFT_JOIN_ON('users', 'users.id = posts.author_id');

        $Query->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id');

        $Query->LEFT_JOIN_ON('categories', 'posts.category_id = categories.id');

        $Query->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');

        $Query->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');

        $Query->GROUP_BY('posts.id');

        if (!empty($this->QueryVars['ORDER_BY'])) $Query->ORDER_BY($this->QueryVars['ORDER_BY'][0], $this->QueryVars['ORDER_BY'][1]);
        if (!empty($this->QueryVars['LIMIT'])) $Query->LIMIT($this->QueryVars['LIMIT']);

        $articles = $Query->FIND_ALL();

        # Loop the article, adding content, tags, category, comments, author
        if (!empty($articles)) {
            foreach ($articles as $i => $article) {
                $articles[$i]['tags']     = $Query->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('post_id', '=', (int)$article['id'])->FIND_ALL();
                $articles[$i]['category'] = $Query->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$article['category_id'])->FIND();
                $articles[$i]['comments'] = $Query->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$article['id'])->FIND_ALL();
                $articles[$i]['author']   = $Query->SELECT('*')->FROM('users')->WHERE('id', '=', (int)$article['author_id'])->FIND();
            }
        }
        return $articles;
    }


    /**
     * Parse the provided query string into an array of callable functions,
     * appending the values to self::$chain.
     *
     * @param  string        $QueryStr
     * @return boolean|Exception
     * @throws Exception if the provided query is invalid, empty, or cannot be parsed
     */
    public function parse() 
    {
        $defaults = [
            'FROM'         => 'posts',
            'SELECT'       => [],
            'AND_WHERE'    => [],
            'OR_WHERE'     => [],
            'ORDER_BY'     => [],
            'LIMIT'        => [],
        ];
    
        # Clean the query string
        $q = trim( trim($this->QueryStr, ':') );
        $q = trim( trim( $q, ':') );

        # Split the string into query statements
        $statements  = array_map('trim', explode(':', $q));

        # Split statements into queries and methods
        $queries = [];
        $methods = [];

        foreach ($statements as $query) {

            # Split the string query into an array by any operator
            $query = array_unique(array_filter(array_map('trim', preg_split("/(\>\=|\<\=|\<|\>|\!\=|\=|\&\&|\|\|)/", $query, NULL, PREG_SPLIT_DELIM_CAPTURE / PREG_SPLIT_NO_EMPTY))));

            # Check if the query is using regex
            $hasRegex = false;
            $newQuery = [];
            foreach ($query as $qur) {
                if (strpos($qur, 'LIKE') !== false) {
                    $hasRegex = true;
                    $rgxQuery = array_filter(array_map('trim', preg_split("/(LIKE)/", $qur, NULL, PREG_SPLIT_DELIM_CAPTURE / PREG_SPLIT_NO_EMPTY)));
                    $newQuery = array_merge($newQuery, $rgxQuery);
                }
                else {
                    $newQuery[] = $qur;
                }
            }
            if ($hasRegex) $query = $newQuery;

            # if a query has less than 3 values it is invalid
            if (count($query) < 3) {
                throw new \Exception('Invalid query supplied QueryParser. The supplied query "'.$this->QueryStr.'" is invalid.');
            }

            # if the query starts with keymap it is an andWhere()/orWhere() function
            # or group of functions
            if (isset(self::$acceptedKeys[$query[0]]) && !count($query) % 2 == 0) {
                $queries[] = $query;
            }

            # if the query starts with an accepted method it is a database method
            # or group of functions
            else if (in_array($query[0], self::$acceptedMethods)) {
                $methods[] = $query;
            }
        }

        # Throw exception if queries and methods are empty
        if (empty($queries) && empty($methods)) {
            throw new \Exception('Invalid query supplied QueryParser. The supplied query "'.$this->QueryStr.'" is invalid.');
        }

        # Loop through queries and append to chain
        foreach ($queries as $queryGroup) {

            # Throw exception if queries were invalid
            if (!$this->appendQueries($queryGroup)) {
                throw new \Exception('Invalid query supplied to QueryParser. The supplied query "'.$this->QueryStr.'" is invalid.');
            }
        }

        # Loop through methods and append to chain
        foreach ($methods as $method) {
            $this->appendMethods($method);
        }

        # Throw exception if queries and methods are empty
        if ($this->QueryVars === $defaults) {
            throw new \Exception('Invalid query supplied QueryParser. The supplied query "'.$this->QueryStr.'" is invalid.');
        }
        return true;
    }


    /**
     * Append a group of queries to self::$chain
     *
     * @return null
     */
    private function appendMethods($method)
    {
        $call = $method[0];
        $op   = '=';
        $val  = $method[2];

        # orderBy and limit need in
        if ($call === 'limit' && is_numeric($val)) {
            $this->QueryVars['LIMIT'] = (int)$val;
        }
        else if ($call === 'orderBy') {
            if (strpos(strtolower($val),',') !== false ) {
                $sort  = array_map('trim', explode(',', $val));
                $order = (strpos(strtolower($sort[1]),'desc') !== false ? 'DESC' : 'ASC');
                $key   = isset(self::$acceptedKeys[$sort[0]]);
                if ($key) $this->QueryVars['ORDER_BY'] = [self::$acceptedKeys[$sort[0]], $order,];
            }
        }
    }

    /**
     * Append a group of queries to self::$chain
     *
     * @return boolean 
     */
    private function appendQueries($queryGroup) 
    {

        $groupFuncs = [];

        foreach ($queryGroup as $i => $value) {

            $groupCount = count($queryGroup);

            # Catch values with multiple query groups
            $isfunc = $value === '||' || $value === '&&';
            $isfunc = $isfunc && ($groupCount > 3 && (($i+1) % 4) == 0);
            $func   = $isfunc && $value === '||' ? 'OR_WHERE' : 'AND_WHERE';
            $op     = in_array($value, self::$acceptedOperators) ? $value : false;
            $key    = !$op && !$isfunc && isset(self::$acceptedKeys[$value]) ? $value : false;
            $val    = !$op && !$isfunc && !$key ? $value : false;

            if ($isfunc) {
                $groupFuncs[]['call'] = $func;
            }
            else if (!empty($groupFuncs) && $op) {
                $groupFuncs[count($groupFuncs)-1]['op'] = $op;
            }
            else if (!empty($groupFuncs) && $key) {
                $groupFuncs[count($groupFuncs)-1]['field'] = $key;
            }
            else if (!empty($groupFuncs) && $val) {
                $groupFuncs[count($groupFuncs)-1]['val'] = $val;
            }

            # Catch values with a single query group
            else {
                $op  = in_array($value, self::$acceptedOperators) ? $value : false;
                $key = !$op && isset(self::$acceptedKeys[$value]) ? $value : false;
                $val = !$op && !$key ? $value : false;
                $groupFuncs[]['call'] = $func;
                
                if ($op) {
                    $groupFuncs[count($groupFuncs)-1]['op'] = $op;
                }
                else if ($key) {
                    $groupFuncs[count($groupFuncs)-1]['field'] = $key;
                }
                else if ($val) {
                    $groupFuncs[count($groupFuncs)-1]['val'] = $val;
                }
            }
        }

        # Validate found queries
        foreach ($groupFuncs as $i => $group) {

            # Methods must have 4 values, key, op, field, value
            if (count($group) !== 4) return false;

            # Methods must be callable
            if (!isset($group['call'])) return false;
            if (!isset($this->QueryVars[$group['call']])) return false;

            # Remove the caller function name
            $key = $group['call'];
            unset($group['call']);

            # Convert the key
            $group['field'] = self::$acceptedKeys[$group['field']];

            # Convert numeric values to integers
            if (is_numeric($group['val'])) $group['val'] = (int)$group['val'];

            # Append the function to the chain
            $this->QueryVars[$key][] = $group;
        }

        # Be sure that Group funcs are not empty
        return !empty($groupFuncs);
    }

}