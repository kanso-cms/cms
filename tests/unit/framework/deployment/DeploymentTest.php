<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\deployment;

use kanso\framework\deployment\Deployment;
use Mockery;
use tests\TestCase;

/**
 * @group unit
 */
class DeploymentTest extends TestCase
{
    /**
     *
     */
    public function testUpdate()
    {
        $webhook  = Mockery::mock('\kanso\framework\deployment\webhooks\Github');

        $deployment = new Deployment($webhook);

        $webhook->shouldReceive('deploy');

        $deployment->update();
    }
}
