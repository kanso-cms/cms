<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Visitor;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class VisitorTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate()
    {
    	$sql = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $this->assertEquals('foo', $visitor->name);
    }

    /**
     *
     */
    public function testSetGet()
    {
       	$sql  = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql);

        $visitor->name = 'baz';

        $this->assertEquals('baz', $visitor->name);
    }

    /**
     *
     */
    public function testHas()
    {
        $sql  = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql);

        $visitor->name = 'baz';

        $this->assertTrue(isset($visitor->name));

        $this->assertFalse(isset($visitor->email));
    }

    /**
     *
     */
    public function testRemove()
    {
        $sql  = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql);

        $visitor->name = 'baz';

        unset($visitor->name);

        $this->assertEquals(null, $visitor->name);
    }

    /**
     *
     */
    public function testAsArray()
    {
        $sql  = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $this->assertEquals(['name' => 'foo'], $visitor->asArray());
    }

    /**
     *
     */
    public function testGenerateId()
    {
        $sql  = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $oldId = $visitor->visitor_id;

		$visitor->regenerateId();

        $this->assertFalse($oldId === $visitor->visitor_id);
    }

    /**
     *
     */
    public function testDeleteEmpty()
    {
        $sql  = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $this->assertFalse($visitor->delete());
    }

    /*/**
     *
     */
    public function testDeleteTrue()
    {
        $sql  = Mockery::mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['id' => 2, 'visitor_id' => 'ggs3432', 'name' => 'foo']);

		$sql->shouldReceive('DELETE_FROM')->with('crm_visitors')->twice()->andReturn($sql);

        $sql->shouldReceive('DELETE_FROM')->with('crm_visits')->once()->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', 'ggs3432')->twice()->andReturn($sql);

		$sql->shouldReceive('QUERY')->times(3)->andReturn(true);

        $this->assertTrue($visitor->delete());
    }

    /**
     *
     */
    public function testSaveNew()
    {
        $cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['email' => 'foo@bar.com']);

        $id = $visitor->id;

        $sql->shouldReceive('INSERT_INTO')->with('crm_visitors')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['email' => 'foo@bar.com'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $this->assertTrue($visitor->save());

        $this->assertEquals(4, $visitor->id);
    }

    /**
     *
     */
    public function testSaveExisting()
    {
        $cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'email' => 'foo@bar.com']);

        $sql->shouldReceive('UPDATE')->with('crm_visitors')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['email' => 'foo@bar.com'])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 3)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $this->assertTrue($visitor->save());
    }

    /**
     *
     */
    public function testAddVisit()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);
    }

    /**
     *
     */
    public function testGetVisit()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $this->assertTrue($visitor->visit()->page === 'foo');
    }

    /**
     *
     */
    public function tesIsLeadFalse()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->assertFalse($visitor->isLead());
    }

    /**
     *
     */
    public function tesIsLeadTrue()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'email' => 'foo']);

        $this->assertTrue($visitor->isLead());
    }

    /**
     *
     */
    public function tesIsFirstVisitTrue()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'email' => 'foo']);

        $this->assertTrue($visitor->isFirstVisit());
    }

    /**
     *
     */
    public function tesIsFirstVisitFalse()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $this->assertFalse($visitor->isFirstVisit());
    }

    /**
     *
     */
    public function testVisits()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 3]]);

        $visits = $visitor->visits();

        $this->assertTrue(is_array($visits) && !empty($visits));
    }

    /**
     *
     */
    public function testCountVisits()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('visitor_id')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 3]]);

        $this->assertTrue($visitor->countVisits() === 1);
    }

    /**
     *
     */
    public function testPreviousVisit()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('LIMIT')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 3]]);

        $visitor->previousVisit();
    }

    /**
     *
     */
    public function testTimeSincePrevVisit()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('LIMIT')->once()->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 3]]);

        $visitor->timeSincePrevVisit();
    }

    /**
     *
     */
    public function testFirstVisit()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['page' => 'foo']);

        $this->assertTrue($visitor->firstVisit()->page === 'foo');
    }

    /**
     *
     */
    public function testHeartBeat()
    {
        $cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['email' => 'foo@bar.com']);

        $id = $visitor->id;

        $sql->shouldReceive('INSERT_INTO')->with('crm_visitors')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['email' => 'foo@bar.com', 'last_active' => time()])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $visitor->heartBeat();

        $this->assertEquals(time(), $visitor->last_active);
    }

    /**
     *
     */
    public function testMakeLead()
    {
        $cHandler = Mockery::mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['email' => 'foo@bar.com']);

        $id = $visitor->id;

        $sql->shouldReceive('INSERT_INTO')->with('crm_visitors')->once()->andReturn($sql);

        $sql->shouldReceive('VALUES')->with(['email' => 'foo', 'name' => 'bar'])->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

        $sql->shouldReceive('connectionHandler')->once()->andReturn($cHandler);

        $cHandler->shouldReceive('lastInsertId')->once()->andReturn(4);

        $visitor->makeLead('foo', 'bar');

        $this->assertEquals('foo', $visitor->email);

        $this->assertEquals('bar', $visitor->name);
    }

    /**
     *
     */
    public function testBouncedTrue()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('visitor_id')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->once()->andReturn([['id' => 3]]);

        $this->assertTrue($visitor->bounced());
    }

    /**
     *
     */
    public function testChannel()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['page' => 'foo?ch=bar']);

        $this->assertTrue($visitor->channel() === 'bar');
    }

    /**
     *
     */
    public function testGrade()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('visitor_id')->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('FIND_ALL')->andReturn([['id' => 3]]);

        $this->assertTrue($visitor->grade() === 1);

        $this->assertTrue($visitor->grade(null, true) === 'visitor');

        $visitor->email = 'foo';

        $this->assertTrue($visitor->grade() === 2);

        $this->assertTrue($visitor->grade(null, true) === 'lead');
    }

    /**
     *
     */
    public function testMedium()
    {
        $sql = Mockery::mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['page' => 'foo?md=bar']);

        $this->assertTrue($visitor->medium() === 'bar');
    }

    /**
     *
     */
    private function saved($sql)
    {
        $sql->shouldReceive('UPDATE')->with('crm_visitors')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['visitor_id' => '342fd', 'name' => 'foo', 'last_active' => time()])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 3)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);
    }

    /**
     *
     */
    private function addVisit($sql)
    {
        $sql->shouldReceive('UPDATE')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['page' => 'foo'])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);

    }
}
