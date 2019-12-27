<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\wrappers\managers;

use kanso\cms\wrappers\managers\PostManager;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class PostManagerTest extends TestCase
{
    /**
     *
     */
    public function testById(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\PostProvider');
        $post     = $this->mock('\kanso\cms\wrappers\Post');
        $manager  = new PostManager($sql, $provider);

        $provider->shouldReceive('byId')->with(44)->once()->andReturn($post);

        $manager->byId(44);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\PostProvider');
        $post     = $this->mock('\kanso\cms\wrappers\Post');
        $manager  = new PostManager($sql, $provider);

        $provider->shouldReceive('byId')->with(44)->once()->andReturn($post);

        $post->shouldReceive('delete')->once()->andReturn(true);

        $manager->delete(44);
    }

    /**
     *
     */
    public function testCreate(): void
    {
        $sql      = $this->mock('\kanso\framework\database\query\Builder');
        $provider = $this->mock('\kanso\cms\wrappers\providers\PostProvider');
        $post     = $this->mock('\kanso\cms\wrappers\Post');
        $manager  = new PostManager($sql, $provider);

        $provider->shouldReceive('create')->with(['foo' => 'bar'])->once()->andReturn($post);

        $manager->create(['foo' => 'bar']);
    }
}
