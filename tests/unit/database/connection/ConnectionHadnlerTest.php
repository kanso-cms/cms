<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\database\connection;

use Mockery;
use tests\TestCase;
use kanso\framework\database\connection\ConnectionHandler;
use kanso\framework\database\connection\Cache;

/**
 * @group unit
 */
class ConnectionHandlerTest extends TestCase
{
	/**
     *
     */
    public function testBind()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo);

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column_key', 'value'])->once();

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll');

        $handler->bind('column_key', 'value');

        $handler->query('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key');
    }

    /**
     *
     */
    public function testBindAgain()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column1_key OR bar_column = :column2_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo);

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column1_key', 'value1']);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column2_key', 'value2']);

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll');

        $handler->bind('column1_key', 'value1');

        $handler->bind('column2_key', 'value2');

        $handler->query($query);
    }

    /**
     *
     */
    public function testBindMore()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column1_key OR bar_column = :column2_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo);

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column1_key', 'value1']);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column2_key', 'value2']);

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll');

        $handler->bindMore(['column1_key' => 'value1', 'column2_key' => 'value2'] );

        $handler->query($query);
    }

    /**
     *
     */
    public function testBindFromQueryArgs()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column1_key OR bar_column = :column2_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo);

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column1_key', 'value1']);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column2_key', 'value2']);

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll');

        $handler->query($query, ['column1_key' => 'value1', 'column2_key' => 'value2']);
    }

    /**
     *
     */
    public function testAllBindings()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column1_key OR bar_column = :column2_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo);

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column1_key', 'value1']);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column2_key', 'value2']);

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll');

        $handler->bind('column1_key', 'value1');

        $handler->query($query, ['column2_key' => 'value2']);
    }

    /**
     *
     */
    public function testWithCaching()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $connection->shouldReceive('pdo')->andReturn($pdo)->once();

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement)->once();

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column_key', 'value'])->once();

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll')->once();

        $handler->query($query, ['column_key' => 'value']);

        $handler->query($query, ['column_key' => 'value']);
    }

    /**
     *
     */
    public function testClearCaching()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$selectQuery = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

    	$deleteQuery = 'DELETE FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $connection->shouldReceive('pdo')->andReturn($pdo)->times(3);

        $pdo->shouldReceive('prepare')->withArgs([$selectQuery])->andReturn($pdoStatement)->twice();

        $pdo->shouldReceive('prepare')->withArgs([$deleteQuery])->andReturn($pdoStatement)->once();

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column_key', 'value'])->times(3);

        $pdoStatement->shouldReceive('execute')->times(3);

        $pdoStatement->shouldReceive('fetchAll')->twice();

        $pdoStatement->shouldReceive('rowCount')->once();

        $handler->query($selectQuery, ['column_key' => 'value']);

        $handler->query($deleteQuery, ['column_key' => 'value']);

        $handler->query($selectQuery, ['column_key' => 'value']);
    }

    /**
     *
     */
    public function testClearCachingDifferentTable()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$selectQuery = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

    	$deleteQuery = 'DELETE FROM prefixed_foo_table_name WHERE foo_column = :column_key';

        $connection->shouldReceive('pdo')->andReturn($pdo)->twice();

        $pdo->shouldReceive('prepare')->withArgs([$selectQuery])->andReturn($pdoStatement)->once();

        $pdo->shouldReceive('prepare')->withArgs([$deleteQuery])->andReturn($pdoStatement)->once();

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column_key', 'value'])->twice();

        $pdoStatement->shouldReceive('execute')->twice();

        $pdoStatement->shouldReceive('fetchAll')->once();

        $pdoStatement->shouldReceive('rowCount')->once();

        $handler->query($selectQuery, ['column_key' => 'value']);

        $handler->query($deleteQuery, ['column_key' => 'value']);

        $handler->query($selectQuery, ['column_key' => 'value']);
    }


    /**
     *
     */
    public function testColumn()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo);

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column_key', 'value'])->once();

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll')->withArgs([\PDO::FETCH_NUM])->andReturn([['foo', 'baz'], ['bar', 'foobaz']]);

        $result = $handler->column('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

        $this->assertEquals(['foo', 'bar'], $result);
    }

    /**
     *
     */
    public function testRow()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo)->once();

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement)->once();

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column_key', 'value'])->once();

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetch')->withArgs([\PDO::FETCH_ASSOC])->andReturn(['foo' => 'baz'])->once();

        $result = $handler->row('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

        $this->assertEquals(['foo' => 'baz'], $result);
    }

    /**
     *
     */
    public function testSingle()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$pdoStatement = Mockery::mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo)->once();

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement)->once();

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column_key', 'value'])->once();

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchColumn')->andReturn(['foo' => 'baz'])->once();

        $result = $handler->single('SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key', ['column_key' => 'value']);

        $this->assertEquals(['foo' => 'baz'], $result);
    }

     /**
     *
     */
    public function testLastInsertedId()
    {
    	$connection = Mockery::mock('\kanso\framework\database\connection\Connection');

    	$pdo = Mockery::mock('\PDO');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo)->once();

        $pdo->shouldReceive('lastInsertId')->once();

        $handler->lastInsertId();
    }
}