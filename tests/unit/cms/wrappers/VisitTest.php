<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Visit;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class VisitTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql, ['name' => 'foo']);

        $this->assertEquals('foo', $visit->name);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql);

        $visit->name = 'baz';

        $this->assertEquals('baz', $visit->name);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql);

        $visit->name = 'baz';

        $this->assertTrue(isset($visit->name));

        $this->assertFalse(isset($visit->page));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql);

        $visit->name = 'baz';

        unset($visit->name);

        $this->assertEquals(null, $visit->name);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql, ['name' => 'foo']);

        $this->assertEquals(['name' => 'foo'], $visit->asArray());
    }

    /**
     *
     */
    public function testDeleteEmpty(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql, ['name' => 'foo']);

        $this->assertFalse($visit->delete());
    }

    /**
     *
     */
    public function testDeleteTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql, ['id' => 2, 'name' => 'foo']);

        $sql->shouldReceive('DELETE_FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->assertTrue($visit->delete());
    }

    /**
     *
     */
    public function testDeleteFalse(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql, ['name' => 'foo']);

        $this->assertFalse($visit->delete());
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql, ['page' => 'crm_visitpage']);

        $sql->shouldReceive('INSERT_INTO')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['page' => 'crm_visitpage'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($visit->save());

        $this->assertEquals(4, $visit->id);
    }

    /**
     *
     */
    public function testSaveExisting(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visit = new Visit($sql, ['id' => 1, 'page' => 'crm_visitpage']);

        $sql->shouldReceive('UPDATE')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['page' => 'crm_visitpage'])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->assertTrue($visit->save());
    }
}
