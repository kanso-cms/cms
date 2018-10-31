<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\mvc;

use kanso\framework\mvc\model\Model;
use kanso\tests\TestCase;

class TestModelCase extends Model
{
    public function foobar()
    {
        return 'foobar';
    }
}

/**
 * @group unit
 * @group framework
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
