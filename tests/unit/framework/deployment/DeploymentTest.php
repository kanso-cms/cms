<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\deployment;

use kanso\framework\deployment\Deployment;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class DeploymentTest extends TestCase
{
    /**
     *
     */
    public function testUpdate(): void
    {
        $webhook  = Mockery::mock('\kanso\framework\deployment\webhooks\Github');

        $deployment = new Deployment($webhook);

        $webhook->shouldReceive('deploy');

        $deployment->update();
    }
}
