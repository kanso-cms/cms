<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\mvc;

use kanso\framework\mvc\controller\Controller;
use kanso\framework\mvc\model\Model;
use Mockery;
use tests\TestCase;

class TestController extends Controller
{
    public function foobar()
    {
        return 'foobar';
    }
}

class TestControllerModel extends Model
{
}

/**
 * @group unit
 */
class ControllerTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate()
    {
        $request = Mockery::mock('\kanso\framework\http\request\Request');

        $response = Mockery::mock('\kanso\framework\http\response\Response');

        $next = function()
        {

        };

        $controller = new TestController($request, $response, $next, '\tests\unit\framework\mvc\TestControllerModel');

        $this->assertEquals('foobar', $controller->foobar());
    }
}
