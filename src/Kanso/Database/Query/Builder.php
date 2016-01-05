<?php

namespace Kanso\Database\Query;

/**
 * Database Builder class
 *
 * This serves as a consistant way to query the databse using PHP
 * without having to write messy SQL everywhere.
 *
 * It provides a centralized way to execute queries
 * on the databse.
 *
 * Most methods are chainable to easily execute multiple
 * SQL satements
 */
class Builder
{

    /**
     * @var \Kanso\Database\Database
     */ 
	private $Database;

    /**
     * @var \Kanso\Database\Query\Query
     */ 
	private $Query;
	
    /**
     * Constructor
     *
     * @param \Kanso\Database\Database $db (optional)
     */
	public function __construct($db = null)
	{
        # Save the database access instance locally
		$this->Database = !$db ? \Kanso\Kanso::getInstance()->Database : $db;

        # create a new query object
        $this->Query = new Query($this->Database);
	}

	/********************************************************************************
    * PUBLIC ACCESS FOR TABLE MANAGEMENT
    *******************************************************************************/

    /**
     * Create a new table with given columns and paramters
     *
     * @param  string    $tableName
     * @param  array     $params
     * @return \Kanso\Database\Query\Builder
     */
    public function CREATE_TABLE($tableName, $params)
    {

        # Filter the tablename
        $tableName  = $this->indexFilter($tableName);
        
        # Reset the id field
        $params['id'] = ' INT | UNSIGNED | UNIQUE | AUTO_INCREMENT';
        
        # Build the SQL
        $SQL = ["CREATE TABLE `$tableName` ("];
        
        # Loop the columns
        foreach ($params as $name => $params) {
            $name  = $this->indexFilter($name);
            $SQL[] = "`$name` ".str_replace('|', '', $params).',';
        }

        # Set default table configuration
        $SQL[] = "PRIMARY KEY (id)\n) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;";
    
        # Execute the query
        $this->Database->query(implode(' ', $SQL));

        # Set the table in the query
        $this->Query->setTable($tableName);

        # Return Builder for chaining
        return $this;
    }

    /**
     * Drop an existing table
     *
     * @param  string    $tableName
     * @return \Kanso\Database\Query\Builder
     */
    public function DROP_TABLE($tableName)
    {
        $tableName = $this->indexFilter($tableName);
        $this->Query->setTable(null);
        $this->Database->query("DROP TABLE $tableName");
        return $this;
    }

    /**
     * Truncate an existing table
     *
     * @param  string    $tableName
     * @return \Kanso\Database\Query\Builder
     */
    public function TRUNCATE_TABLE($tableName)
    {
        $tableName  = $this->indexFilter($tableName);
        $this->Query->setTable($tableName);
        $this->Database->query("TRUNCATE TABLE $tableName"); 
        return $this;
    }

    /**
     * Initialize an alter statement
     *
     * @param  string    $tableName
     * @return \Kanso\Database\Query\Alter
     */
    public function ALTER_TABLE($tableName)
    {
        $tableName  = $this->indexFilter($tableName);
        $this->Query->setTable($tableName);
        return new Alter($tableName, $this->Database);
    }

    /********************************************************************************
    * PUBLIC ACCESS FOR ROW/DATA MANAGEMENT
    *******************************************************************************/

    /**
     * Set the query to query a given table
     *
     * @param  string    $table
     * @return \Kanso\Database\Query\Builder
     */
    public function FROM($table)
    {
        $table = $this->indexFilter($table);
        $this->Query->setTable($table);
        $this->Query->setOperation('QUERY');        
        return $this;
    }

    /**
     * Set the query to UPDATE a given table
     *
     * @param  string    $table
     * @return \Kanso\Database\Query\Builder
     */
    public function UPDATE($table)
    {
        $table  = $this->indexFilter($table);
        $this->Query->setTable($table);
        return $this;
    }

    /**
     * Set the query to INSERT INTO a given table
     *
     * @param  string    $table
     * @return \Kanso\Database\Query\Builder
     */
    public function INSERT_INTO($table)
    {
        $table = $this->indexFilter($table);
        $this->Query->setTable($table);
        $this->Query->setOperation('INSERT INTO');
        return $this;
    }

    /**
     * Set the query to INSERT INTO and load values
     *
     * @param  string    $values
     * @return \Kanso\Database\Query\Builder
     */
    public function VALUES($values)
    {
        $this->Query->setOperation('INSERT INTO', $values);
        return $this;
    }

    /**
     * Set the query to SET and load values
     *
     * @param  string    $values
     * @return \Kanso\Database\Query\Builder
     */
    public function SET($values) 
    {
        $this->Query->setOperation('SET', $values);
        return $this;
    }

    /**
     * Set the query to DELETE and load table
     *
     * @param  string    $table
     * @return \Kanso\Database\Query\Builder
     */
    public function DELETE_FROM($table)
    {
        $table  = $this->indexFilter($table);
        $this->Query->setTable($table);
        $this->Query->setOperation('DELETE');
        return $this;
    }

    /**
     * Execute an INSERT, DELETE, UPDATE, SET statement
     *
     * @return array   Result from the SQL query
     */
    public function QUERY()
    {
        return $this->Query->query();
    }
    
    /********************************************************************************
    * PUBLIC ACCESS FOR QUERIES
    *******************************************************************************/

    /**
     * Select values from a table
     *
     * @param  string    $values
     * @return \Kanso\Database\Query\Builder
     */
    public function SELECT($values)
    {
        $this->Query->select($values);
        return $this;
    }

    /**
     * Set a where clause
     *
     * @param  string    $column
     * @param  string    $op
     * @param  string    $value
     * @return \Kanso\Database\Query\Builder
     */
    public function WHERE($column, $op, $value)
    {
        $this->Query->where($column, $op, $value);
        return $this;
    }

    /**
     * Set an and_where clause
     *
     * @param  string    $column
     * @param  string    $op
     * @param  string    $value
     * @return \Kanso\Database\Query\Builder
     */
    public function AND_WHERE($column, $op, $value)
    {
        $this->Query->and_where($column, $op, $value);
        return $this;
    }

    /**
     * Set an or_where clause
     *
     * @param  string    $column
     * @param  string    $op
     * @param  string    $value
     * @return \Kanso\Database\Query\Builder
     */
    public function OR_WHERE($column, $op, $value)
    {
        $this->Query->or_where($column, $op, $value);
        return $this;
    }   

    /**
     * Set an join clause
     *
     * @param  string    $table
     * @param  string    $query
     * @return \Kanso\Database\Query\Builder
     */
    public function JOIN_ON($table, $query)
    {
        $this->Query->join($table, $query);
        return $this;
    }

    /**
     * Set an inner join clause
     *
     * @param  string    $table
     * @param  string    $query
     * @return \Kanso\Database\Query\Builder
     */
    public function INNER_JOIN_ON($table, $query)
    {
        $this->Query->join($table, $query);
        return $this;
    }

    /**
     * Set a left join clause
     *
     * @param  string    $table
     * @param  string    $query
     * @return \Kanso\Database\Query\Builder
     */
    public function LEFT_JOIN_ON($table, $query)
    {
        $this->Query->left_join($table, $query);
        return $this;
    }

    /**
     * Set a right join clause
     *
     * @param  string    $table
     * @param  string    $query
     * @return \Kanso\Database\Query\Builder
     */
    public function RIGHT_JOIN_ON($table, $query)
    {
        $this->Query->right_join($table, $query);
        return $this;
    }

    /**
     * Set an outer join clause
     *
     * @param  string    $table
     * @param  string    $query
     * @return \Kanso\Database\Query\Builder
     */
    public function OUTER_JOIN_ON($table, $query)
    {
        $this->Query->full_outer_join($table, $query);
        return $this;
    }

    /**
     * Set the orderby
     *
     * @param  string    $key
     * @param  string    $direction
     * @return \Kanso\Database\Query\Builder
     */
    public function ORDER_BY($key, $direction = 'DESC')
    {
        $this->Query->order_by($key, $direction);
        return $this;
    }

    /**
     * Set group by
     *
     * @param  string    $key
     * @param  string    $direction
     * @return \Kanso\Database\Query\Builder
     */
    public function GROUP_BY($key)
    {
        $this->Query->group_by($key);
        return $this;
    }

    /**
     * Add group concat
     *
     * @param  string    $keys
     * @param  string    $as
     * @return \Kanso\Database\Query\Builder
     */
    public function GROUP_CONCAT($keys, $as)
    {
        $this->Query->group_concat($keys, $as);
        return $this;
    }

    /**
     * Set the limit
     *
     * @param  string    $keys
     * @param  string    $as
     * @return \Kanso\Database\Query\Builder
     */
    public function LIMIT($value)
    {
        $this->Query->limit($value);
        return $this;
    }

    /**
     * Execute a query and limit to single row
     *
     * @return \Kanso\Database\Query\Builder
     */
    public function ROW()
    {
        return $this->Query->row();
    }

    /**
     * Execute a query and limit to single row 
     * and/or find a single row by id
     *
     * @param  int    $id
     * @return \Kanso\Database\Query\Builder
     */
    public function FIND($id = null)
    {
        return $this->Query->find($id);
    }

    /**
     * Execute a query and find all rows
     *
     * @return \Kanso\Database\Query\Builder
     */
    public function FIND_ALL()
    {
        return $this->Query->find_all();
    }

    /********************************************************************************
    * PRE-MADE QUERIES
    *******************************************************************************/

    /**
     * Get articles/article by index
     *
     * This method provides a consisten way to retreive articles from
     * the database while joining various other tables
     *
     * @param   str    $index
     * @param   str    $value
     * @param   int    $limit
     * @return  array
     */
    public function getArticlesByIndex($index = null, $value = null, $limit = null, $joins = [])
    {
     
        # Get a new Builder 
        $Query = new Builder();

        # Select the posts
        $Query->SELECT('*')->FROM('posts');

        # If clauses were supplied, set queries
        if ($index && $value) $Query->WHERE($index, '=', $value);

        # If a limit was supplied set a limit
        if ($limit) $Query->LIMIT((int)$limit);

        # Find the articles
        $articles = $Query->FIND_ALL();

        if (!empty($joins)) {

            # Flip the joins 
            $joins    = array_flip($joins);

            # Set what joins are needed so we don't need to check on each loop
            $joinTags   = isset($joins['tags']);
            $joinCats   = isset($joins['category']);
            $joinCont   = isset($joins['content']);
            $joinComts  = isset($joins['comments']);
            $joinAuthor = isset($joins['author']);

            # Loop the article, adding content, tags, category, comments, author
            if (!empty($articles)) {
                if (!$this->isMulti($articles)) {
                    if ($joinTags)   $articles['tags']     = $Query->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('tags_to_posts.post_id', '=', (int)$articles['id'])->FIND_ALL();
                    if ($joinCats)   $articles['category'] = $Query->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$articles['category_id'])->FIND();
                    if ($joinCont)   $articles['content']  = $Query->SELECT('content')->FROM('content_to_posts')->WHERE('post_id', '=', (int)$articles['id'])->FIND()['content'];
                    if ($joinComts)  $articles['comments'] = $Query->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$articles['id'])->FIND_ALL();
                    if ($joinAuthor) $articles['author']   = $Query->SELECT('*')->FROM('authors')->WHERE('id', '=', (int)$articles['author_id'])->FIND();

                }
                else {
                    foreach ($articles as $i => $article) {
                        if ($joinTags)   $articles[$i]['tags']     = $Query->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')->WHERE('post_id', '=', (int)$article['id'])->FIND_ALL();
                        if ($joinCats)   $articles[$i]['category'] = $Query->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$article['category_id'])->FIND();
                        if ($joinCont)   $articles[$i]['content']  = $Query->SELECT('content')->FROM('content_to_posts')->WHERE('post_id', '=', (int)$article['id'])->FIND()['content'];
                        if ($joinComts)  $articles[$i]['comments'] = $Query->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$article['id'])->FIND_ALL();
                        if ($joinAuthor) $articles[$i]['author']   = $Query->SELECT('*')->FROM('authors')->WHERE('id', '=', (int)$article['author_id'])->FIND();
                    }
                }
            }
        }

        # Return the articles
        return $articles;
    }

    /**
     * Get article by id
     *
     * This method provides a consisten way to retreiving a single article from
     * the database while joining various other tables by id
     *
     * @param   str    $index
     * @param   str    $value
     * @param   int    $limit
     * @return  array
     */
    public function getArticleById($article_id)
    {
        return $this->getArticlesByIndex('id', (int)$article_id, 1);
    }

    private function isMulti($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) return true;
        }
        return false;
    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Filter a column name to valid SQL
     *
     * @param   str    $str
     * @return str
     */
    private function indexFilter($str)
    {
        return strtolower(str_replace(' ', '_', $str));
    }


}