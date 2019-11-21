<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

use kanso\framework\utility\Str;

/**
 * Filter static page.
 *
 * @author Joe J. Howard
 */
class Page extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $slug      = Str::getBeforeLastWord($this->Request->environment()->REQUEST_PATH, '/feed');
        $status    = $this->Request->fetch('query') === 'draft' && $this->Gatekeeper->isAdmin() ? 'draft' : 'published';
        $queryStr  = "post_status = $status : post_type = page : post_slug = {$slug}/";
        $posts     = $this->parseQueryStr($queryStr);
        $postCount = count($posts);

        if ($postCount === 0)
        {
            return false;
        }

        $this->Query->requestType = $this->requestType();
        $this->Query->queryStr    = $queryStr;
        $this->Query->posts       = $posts;
        $this->Query->postCount   = $postCount;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function requestType(): string
    {
        return 'page';
    }
}
