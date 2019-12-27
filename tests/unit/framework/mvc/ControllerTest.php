<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\mvc;

use kanso\framework\mvc\controller\Controller;
use kanso\framework\mvc\model\Model;
use kanso\tests\TestCase;

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
 * @group framework
 */
class ControllerTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $request = $this->mock('\kanso\framework\http\request\Request');

        $response = $this->mock('\kanso\framework\http\response\Response');

        $next = function(): void
        {

        };

        $controller = new TestController($request, $response, $next, 'kanso\tests\unit\framework\mvc\TestControllerModel');

        $this->assertEquals('foobar', $controller->foobar());
    }
}
