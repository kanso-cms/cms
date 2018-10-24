<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\cms\wrappers\managers;

use kanso\cms\wrappers\managers\PostManager;
use Mockery;
use tests\TestCase;

/**
 * @group unit
 */
class PostManagerTest extends TestCase
{
    /**
     *
     */
    public function testById()
    {
        $sql      = Mockery::mock('\kanso\framework\database\query\Builder');
        $provider = Mockery::mock('\kanso\cms\wrappers\providers\PostProvider');
        $post     = Mockery::mock('\kanso\cms\wrappers\Post');
        $manager  = new PostManager($sql, $provider);

        $provider->shouldReceive('byId')->with(44)->once()->andReturn($post);

        $manager->byId(44);
    }

    /**
     *
     */
    public function testDelete()
    {
        $sql      = Mockery::mock('\kanso\framework\database\query\Builder');
        $provider = Mockery::mock('\kanso\cms\wrappers\providers\PostProvider');
        $post     = Mockery::mock('\kanso\cms\wrappers\Post');
        $manager  = new PostManager($sql, $provider);

        $provider->shouldReceive('byId')->with(44)->once()->andReturn($post);

        $post->shouldReceive('delete')->once()->andReturn(true);

        $manager->delete(44);
    }

    /**
     *
     */
    public function testCreate()
    {
        $sql      = Mockery::mock('\kanso\framework\database\query\Builder');
        $provider = Mockery::mock('\kanso\cms\wrappers\providers\PostProvider');
        $post     = Mockery::mock('\kanso\cms\wrappers\Post');
        $manager  = new PostManager($sql, $provider);

        $provider->shouldReceive('create')->with(['foo' => 'bar'])->once()->andReturn($post);

        $manager->create(['foo' => 'bar']);
    }
}
