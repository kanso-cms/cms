<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\mvc;

use tests\TestCase;
use kanso\framework\mvc\view\View;

/**
 * @group unit
 */
class ViewTest extends TestCase
{
    /**
     *
     */
    private function getInclude()
    {
        $handle = tmpfile();
                
        $path = stream_get_meta_data($handle)['uri'];

        fclose($handle);

        file_put_contents($path, '<?php $foo = "bar"; ?>');

        rename($path, $path.'.php');

        return $path.'.php';
    }

    /**
     *
     */
    private function getTemplate()
    {
        $handle = tmpfile();
                
        $path = stream_get_meta_data($handle)['uri'];

        fclose($handle);

        file_put_contents($path, '<?php echo $foo; ?>');

        rename($path, $path.'.php');

        return $path.'.php';
    }

    /**
     *
     */
    public function testDisplay()
    {
        $view = new View;

        $view->includeKanso(false);

        $this->assertEquals('bar', $view->display($this->getTemplate(), ['foo' => 'bar']));
    }

    /**
     *
     */
    public function testInclude()
    {
        $view = new View;

        $view->includeKanso(false);

        $view->include($this->getInclude());

        $this->assertEquals('bar', $view->display($this->getTemplate()));
    }

    /**
     *
     */
    public function testArrayAccess()
    {
        $view = new View;

        $view->includeKanso(false);

        $view->set('foo', 'foobaz');

        $this->assertEquals('foobaz', $view->display($this->getTemplate()));
    }
}
