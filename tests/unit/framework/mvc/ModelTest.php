<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\mvc;

use tests\TestCase;
use kanso\framework\mvc\model\Model;

class TestModelCase extends Model
{
    public function foobar()
    {
        return 'foobar';
    }
}

/**
 * @group unit
 */
class ModelTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate()
    {
        $model = new TestModelCase;

        $this->assertEquals('foobar', $model->foobar());
    }
}
