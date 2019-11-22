<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\controllers;

use Closure;
use kanso\cms\query\models\SingleCustom;
use kanso\cms\rss\Feed;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\mvc\controller\Controller;

/**
 * Custom posts controller.
 *
 * @author Joe J. Howard
 */
class CustomPost extends Controller
{
    /**
     * Post type.
     *
     * @var string
     */
    private $requestType;

    /**
     * Constructor.
     *
     * @param \kanso\framework\http\request\Request   $request     Request instance
     * @param \kanso\framework\http\response\Response $response    Response instance
     * @param \Closure                                $next        Next middleware closure
     * @param string                                  $requestType The custom post type
     */
    public function __construct(Request $request, Response $response, Closure $next, string $requestType)
    {
        $this->nextMiddleware = $next;

        $this->requestType = $requestType;

        $this->model = new SingleCustom;

        $this->model->setRequestType($requestType);
    }

    /**
     * Loads an RSS route.
     */
    public function load(): void
    {
        $filter   = $this->model->filter();
        $template = $this->getTemplate();

        if ($filter && $template)
        {
            $this->Response->disableCaching();

            // Set the_post so we're looking at the first item
            if (isset($this->Query->posts[0]))
            {
                $this->Query->post = $this->Query->posts[0];
            }

            $this->fileResponse($template);
        }
        else
        {
            $this->Query->reset();

            $this->nextMiddleware();
        }
    }

    /**
     * Loads an RSS route.
     */
    public function rss(): void
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

    /**
     * Determine what template to use.
     *
     * @return string|false
     */
    private function getTemplate()
    {
        $themeDir  = $this->Config->get('cms.themes_path') . '/' . $this->Config->get('cms.theme_name');
        $urlParts  = explode('/', $this->Request->environment()->REQUEST_PATH);
        $waterfall =
        [
            'single-' . array_pop($urlParts),
            $this->requestType,
            'single',

        ];

        foreach ($waterfall as $name)
        {
            $template = "{$themeDir}/$name.php";

            if ($this->Filesystem->exists($template))
            {
                return $template;
            }
        }

        return false;
    }
}
