<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\database\connection;

use kanso\framework\database\connection\Cache;
use kanso\framework\database\connection\ConnectionHandler;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class ConnectionHandlerTest extends TestCase
{
    /**
     *
     */
    public function testBind(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

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
    public function testBindAgain(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

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
    public function testBindMore(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column1_key OR bar_column = :column2_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo);

        $pdo->shouldReceive('prepare')->withArgs([$query])->andReturn($pdoStatement);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column1_key', 'value1']);

        $pdoStatement->shouldReceive('bindParam')->withArgs([':column2_key', 'value2']);

        $pdoStatement->shouldReceive('execute')->once();

        $pdoStatement->shouldReceive('fetchAll');

        $handler->bindMore(['column1_key' => 'value1', 'column2_key' => 'value2']);

        $handler->query($query);
    }

    /**
     *
     */
    public function testBindFromQueryArgs(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

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
    public function testAllBindings(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

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
    public function testWithCaching(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

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
    public function testClearCaching(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

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
    public function testClearCachingDifferentTable(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$pdoStatement = $this->mock('\PDOStatement');

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
    public function testLastInsertedId(): void
    {
    	$connection = $this->mock('\kanso\framework\database\connection\Connection');

    	$pdo = $this->mock('\PDO');

    	$handler = new ConnectionHandler($connection, new Cache);

    	$query = 'SELECT * FROM prefixed_my_table_name WHERE foo_column = :column_key';

        $handler->cache()->disable();

        $connection->shouldReceive('pdo')->andReturn($pdo)->once();

        $pdo->shouldReceive('lastInsertId')->once();

        $handler->lastInsertId();
    }
}
