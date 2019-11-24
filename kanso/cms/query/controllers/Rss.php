<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\controllers;

use kanso\cms\rss\Feed;
use kanso\framework\mvc\controller\Controller;

/**
 * CMS Query Dispatcher.
 *
 * @author Joe J. Howard
 */
class Rss extends Controller
{
    /**
     * Loads an RSS route.
     */
    public function load(): void
    {
        if ($this->model->filter())
        {
            $format = explode('/', $this->Request->environment()->REQUEST_PATH);

            $rss = new Feed($this->Request, $this->Response, array_pop($format));

            $rss->render();
        }
        else
        {
            $this->nextMiddleware();
        }
    }
}
