<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers;

use kanso\cms\wrappers\Visitor;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class VisitorTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
    	$sql = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $this->assertEquals('foo', $visitor->name);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
       	$sql  = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql);

        $visitor->name = 'baz';

        $this->assertEquals('baz', $visitor->name);
    }

    /**
     *
     */
    public function testHas(): void
    {
        $sql  = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql);

        $visitor->name = 'baz';

        $this->assertTrue(isset($visitor->name));

        $this->assertFalse(isset($visitor->email));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $sql  = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql);

        $visitor->name = 'baz';

        unset($visitor->name);

        $this->assertEquals(null, $visitor->name);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $sql  = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $this->assertEquals(['name' => 'foo'], $visitor->asArray());
    }

    /**
     *
     */
    public function testGenerateId(): void
    {
        $sql  = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $oldId = $visitor->visitor_id;

		$visitor->regenerateId();

        $this->assertFalse($oldId === $visitor->visitor_id);
    }

    /**
     *
     */
    public function testDeleteEmpty(): void
    {
        $sql  = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['name' => 'foo']);

        $this->assertFalse($visitor->delete());
    }

    /*/**
     *
     */
    public function testDeleteTrue(): void
    {
        $sql  = $this->mock('\kanso\framework\database\query\Builder');

		$visitor = new Visitor($sql, ['id' => 2, 'visitor_id' => 'ggs3432', 'name' => 'foo']);

		$sql->shouldReceive('DELETE_FROM')->with('crm_visitors')->twice()->andReturn($sql);

        $sql->shouldReceive('DELETE_FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('DELETE_FROM')->with('crm_visit_actions')->once()->andReturn($sql);

		$sql->shouldReceive('WHERE')->with('id', '=', 2)->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', 'ggs3432')->times(3)->andReturn($sql);

		$sql->shouldReceive('QUERY')->times(4)->andReturn(true);

        $this->assertTrue($visitor->delete());
    }

    /**
     *
     */
    public function testSaveNew(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testSaveExisting(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testAddVisit(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);
    }

    /**
     *
     */
    public function testGetVisit(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $this->assertTrue($visitor->visit()->page === 'foo');
    }

    /**
     *
     */
    public function tesIsLeadFalse(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->assertFalse($visitor->isLead());
    }

    /**
     *
     */
    public function tesIsLeadTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'email' => 'foo']);

        $this->assertTrue($visitor->isLead());
    }

    /**
     *
     */
    public function tesIsFirstVisitTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'email' => 'foo']);

        $this->assertTrue($visitor->isFirstVisit());
    }

    /**
     *
     */
    public function tesIsFirstVisitFalse(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $this->assertFalse($visitor->isFirstVisit());
    }

    /**
     *
     */
    public function testVisits(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testCountVisits(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testPreviousVisit(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('LIMIT')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 3]);

        $visitor->previousVisit();
    }

    /**
     *
     */
    public function testTimeSincePrevVisit(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

        $visitor = new Visitor($sql, ['id' => 3, 'visitor_id' => '342fd', 'name' => 'foo']);

        $this->addVisit($sql);

        $this->saved($sql);

        $visitor->addVisit(['id' => 1, 'page' => 'foo']);

        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->once()->andReturn($sql);

        $sql->shouldReceive('LIMIT')->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(['id' => 3]);

        $visitor->timeSincePrevVisit();
    }

    /**
     *
     */
    public function testFirstVisit(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testHeartBeat(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testMakeLead(): void
    {
        $cHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testBouncedTrue(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testChannel(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testGrade(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    public function testMedium(): void
    {
        $sql = $this->mock('\kanso\framework\database\query\Builder');

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
    private function saved($sql): void
    {
        $sql->shouldReceive('UPDATE')->with('crm_visitors')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['visitor_id' => '342fd', 'name' => 'foo', 'last_active' => time()])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 3)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);
    }

    /**
     *
     */
    private function addVisit($sql): void
    {
        $sql->shouldReceive('SELECT')->with('*')->once()->andReturn($sql);

        $sql->shouldReceive('FROM')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('visitor_id', '=', '342fd')->once()->andReturn($sql);

        $sql->shouldReceive('ORDER_BY')->with('date', 'DESC')->once()->andReturn($sql);

        $sql->shouldReceive('LIMIT')->with(1, 1)->once()->andReturn($sql);

        $sql->shouldReceive('ROW')->once()->andReturn(0);

        $sql->shouldReceive('UPDATE')->with('crm_visits')->once()->andReturn($sql);

        $sql->shouldReceive('SET')->with(['page' => 'foo'])->once()->andReturn($sql);

        $sql->shouldReceive('WHERE')->with('id', '=', 1)->once()->andReturn($sql);

        $sql->shouldReceive('QUERY')->once()->andReturn(true);
    }
}
