<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\database\query;

use kanso\framework\database\query\Builder;
use kanso\framework\database\query\Query;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class BuilderTest extends TestCase
{
    /**
     *
     */
    public function testCreateTable()
    {
        $tableConfig =
        [
            'id'            => 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT',
            'description'   => 'VARCHAR(255)',
            'thumbnail_id'  => 'INTEGER | UNSIGNED',
            'notifications' => 'BOOLEAN | DEFAULT TRUE',
        ];

        $query = 'CREATE TABLE `prefixed_my_table_name` ( `id` INT UNSIGNED UNIQUE AUTO_INCREMENT, `description` VARCHAR(255), `thumbnail_id` INTEGER UNSIGNED, `notifications` BOOLEAN DEFAULT TRUE, PRIMARY KEY (id) ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->CREATE_TABLE('my_table_name', $tableConfig);
    }

    /**
     *
     */
    public function testDropTable()
    {
        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['DROP TABLE `prefixed_my_table_name`']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->DROP_TABLE('my_table_name');
    }

    /**
     *
     */
    public function testTruncateTable()
    {
        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['TRUNCATE TABLE `prefixed_my_table_name`']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->TRUNCATE_TABLE('my_table_name');
    }

    /**
     *
     */
    public function testDelete()
    {
        $query = 'DELETE FROM prefixed_my_table_name WHERE foo = :prefixedmytablenameandfoobar';

        $bindings = ['prefixedmytablenameandfoobar' => 'bar'];

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, $bindings]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->DELETE_FROM('my_table_name')->WHERE('foo', '=', 'bar')->QUERY();
    }

    /**
     *
     */
    public function testUpdate()
    {
        $query = 'UPDATE prefixed_my_table_name SET column = :column WHERE foo = :prefixedmytablenameandfoobar';

        $bindings = ['prefixedmytablenameandfoobar' => 'bar', 'column' => 'value'];

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, $bindings]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->UPDATE('my_table_name')->SET(['column' => 'value'])->WHERE('foo', '=', 'bar')->QUERY();
    }

    /**
     *
     */
    public function testInsertInto()
    {
        $query = 'INSERT INTO prefixed_my_table_name (column1, column2) VALUES(:column1, :column2)';

        $bindings = ['column1' => 'value1', 'column2' => 'value2'];

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, $bindings]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->INSERT_INTO('my_table_name')->values(['column1' => 'value1', 'column2' => 'value2'])->QUERY();
    }

    /**
     *
     */
    public function testSelectAll()
    {
        $query = 'SELECT * FROM prefixed_my_table_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testSelectColumns()
    {
        $query = 'SELECT id, name FROM prefixed_my_table_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('id,name')->FROM('my_table_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testSelectRow()
    {
        $query = 'SELECT * FROM prefixed_my_table_name LIMIT 1';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->ROW();
    }

    /**
     *
     */
    public function testSelectFind()
    {
        $query = 'SELECT * FROM prefixed_my_table_name LIMIT 1';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->FIND();
    }

    /**
     *
     */
    public function testWhere()
    {
        $query = 'SELECT * FROM prefixed_my_table_name WHERE foo = :prefixedmytablenameandfoobar';

        $bindings = ['prefixedmytablenameandfoobar' => 'bar'];

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, $bindings]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', 'bar')->FIND_ALL();
    }

    /**
     *
     */
    public function testOrWhere()
    {
        $query = 'SELECT * FROM prefixed_my_table_name WHERE foo = :prefixedmytablenameandfoobar OR bar = :prefixedmytablenameorbarfoo';

        $bindings = ['prefixedmytablenameandfoobar' => 'bar', 'prefixedmytablenameorbarfoo' => 'foo'];

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, $bindings]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', 'bar')->OR_WHERE('bar', '=', 'foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testAndWhere()
    {
        $query = 'SELECT * FROM prefixed_my_table_name WHERE foo = :prefixedmytablenameandfoobar AND bar = :prefixedmytablenameandbarfoo';

        $bindings = ['prefixedmytablenameandfoobar' => 'bar', 'prefixedmytablenameandbarfoo' => 'foo'];

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, $bindings]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', 'bar')->AND_WHERE('bar', '=', 'foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testNestedOrWhere()
    {
        $query = 'SELECT * FROM prefixed_my_table_name WHERE (foo = :prefixedmytablenameandfoofoo OR foo = :prefixedmytablenameandfoobar OR foo = :prefixedmytablenameandfoofoobaz)';

        $bindings = ['prefixedmytablenameandfoofoo' => 'foo', 'prefixedmytablenameandfoobar' => 'bar', 'prefixedmytablenameandfoofoobaz' => 'foobaz'];

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, $bindings]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->WHERE('foo', '=', ['foo', 'bar', 'foobaz'])->FIND_ALL();
    }

    /**
     *
     */
    public function testJoinOn()
    {
        $query = 'SELECT * FROM prefixed_my_table_name INNER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testInnerJoinOn()
    {
        $query = 'SELECT * FROM prefixed_my_table_name INNER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->INNER_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testLeftJoinOn()
    {
        $query = 'SELECT * FROM prefixed_my_table_name LEFT JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->LEFT_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testRightJoinOn()
    {
        $query = 'SELECT * FROM prefixed_my_table_name RIGHT JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->RIGHT_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testOutJoinOn()
    {
        $query = 'SELECT * FROM prefixed_my_table_name FULL OUTER JOIN prefixed_foo_table ON prefixed_table1.column_name = prefixed_table2.column_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->OUTER_JOIN_ON('foo_table', 'table1.column_name = table2.column_name')->FIND_ALL();
    }

    /**
     *
     */
    public function testOrder()
    {
        $query = 'SELECT * FROM prefixed_my_table_name ORDER BY foo DESC';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->ORDER_BY('foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testOrderAsc()
    {
        $query = 'SELECT * FROM prefixed_my_table_name ORDER BY foo ASC';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->ORDER_BY('foo', 'ASC')->FIND_ALL();
    }

    /**
     *
     */
    public function testGroupBy()
    {
        $query = 'SELECT * FROM prefixed_my_table_name GROUP BY foo';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->GROUP_BY('foo')->FIND_ALL();
    }

    /**
     *
     */
    public function testGroupConcat()
    {
        $query = 'SELECT *, GROUP_CONCAT(foo) AS bar FROM prefixed_my_table_name';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->GROUP_CONCAT('foo', 'bar')->FIND_ALL();
    }

    /**
     *
     */
    public function testLimit()
    {
        $query = 'SELECT * FROM prefixed_my_table_name LIMIT 1';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->LIMIT(1)->FIND_ALL();
    }

    /**
     *
     */
    public function testOffset()
    {
        $query = 'SELECT * FROM prefixed_my_table_name LIMIT 0, 3';

        $connectionHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query, []]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->SELECT('*')->FROM('my_table_name')->LIMIT(0, 3)->FIND_ALL();
    }
}
