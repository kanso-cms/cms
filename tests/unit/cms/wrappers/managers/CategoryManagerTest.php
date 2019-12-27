<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers\managers;

use kanso\cms\wrappers\managers\CategoryManager;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class CategoryManagerTest extends TestCase
{
    /**
     *
     */
    public function testCreate(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $cat      = $this->mock('\kanso\cms\wrappers\Category');
        $manager  = new CategoryManager($sql, $provider);

        $provider->shouldReceive('byKey')->with('slug', 'bar', true)->once()->andReturn(null);

        $provider->shouldReceive('create')->with(['name' => 'foo', 'slug' => 'bar'])->once()->andReturn($cat);

        $manager->create('foo', 'bar');
    }

    /**
     *
     */
    public function testById(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $cat      = $this->mock('\kanso\cms\wrappers\Category');
        $manager  = new CategoryManager($sql, $provider);

        $provider->shouldReceive('byId')->with(44)->once()->andReturn($cat);

        $manager->byId(44);
    }

    /**
     *
     */
    public function testByName(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $cat      = $this->mock('\kanso\cms\wrappers\Category');
        $manager  = new CategoryManager($sql, $provider);

        $provider->shouldReceive('byKey')->with('name', 'foo', true)->once()->andReturn($cat);

        $manager->byName('foo');
    }

    /**
     *
     */
    public function testBySlug(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $cat      = $this->mock('\kanso\cms\wrappers\Category');
        $manager  = new CategoryManager($sql, $provider);

        $provider->shouldReceive('byKey')->with('slug', 'foo', true)->once()->andReturn($cat);

        $manager->bySlug('foo');
    }

    /**
     *
     */
    public function testDelete(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $cat      = $this->mock('\kanso\cms\wrappers\Category');
        $manager  = new CategoryManager($sql, $provider);

        $provider->shouldReceive('byId')->with(44)->once()->andReturn($cat);

        $cat->shouldReceive('delete')->once();

        $manager->delete(44);

        $provider->shouldReceive('byKey')->with('name', 'foo', true)->once()->andReturn($cat);

        $cat->shouldReceive('delete')->once();

        $manager->delete('foo');

        $provider->shouldReceive('byKey')->with('name', 'bar', true)->once()->andReturn([]);

        $provider->shouldReceive('byKey')->with('slug', 'bar', true)->once()->andReturn($cat);

        $cat->shouldReceive('delete')->once();

        $manager->delete('bar');
    }

    /**
     *
     */
    public function testClear(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\CategoryProvider');
        $cat      = $this->mock('\kanso\cms\wrappers\Category');
        $manager  = new CategoryManager($sql, $provider);

        $provider->shouldReceive('byId')->with(44)->once()->andReturn($cat);

        $cat->shouldReceive('clear')->once();

        $manager->clear(44);

        $provider->shouldReceive('byKey')->with('name', 'foo', true)->once()->andReturn($cat);

        $cat->shouldReceive('clear')->once();

        $manager->clear('foo');

        $provider->shouldReceive('byKey')->with('name', 'bar', true)->once()->andReturn([]);

        $provider->shouldReceive('byKey')->with('slug', 'bar', true)->once()->andReturn($cat);

        $cat->shouldReceive('clear')->once();

        $manager->clear('bar');
    }
}
