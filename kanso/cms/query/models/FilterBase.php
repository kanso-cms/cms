<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

use kanso\framework\common\SqlBuilderTrait;
use kanso\framework\ioc\ContainerAwareTrait;

/**
 * CMS query filter base.
 *
 * @author Joe J. Howard
 */
abstract class FilterBase
{
    use SqlBuilderTrait;
    use ContainerAwareTrait;

    /**
     * Blog location.
     *
     * @var string
     */
    protected $blogLocation;

    /**
     * URL path split into pieces.
     *
     * @var array
     */
    protected $urlParts;

    /**
     * Posts per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * Posts per page.
     *
     * @var int
     */
    protected $offset;

    /**
     * The request type
     *
     * @var string
     */
    protected $requestType = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->blogLocation = $this->Config->get('cms.blog_location');

        $this->urlParts = explode('/', $this->Request->environment()->REQUEST_PATH);

        $this->perPage = $this->Config->get('cms.posts_per_page');

        $this->offset = $this->Query->pageIndex * $this->perPage;
    }

    /**
     * Returns the request type
     * 
     * @return string
     */
    public function requestType(): string
    {
        return $this->requestType;
    }

    /**
     * Set request type.
     */
    public function setRequestType(string $type): void
    {
        $this->requestType = $type;
    }

    /**
     * Parse a query string.
     *
     * @return array
     */
    protected function parseQueryStr(string $queryStr): array
    {
        return $this->Query->helper('parser')->parseQuery($queryStr);
    }
}
