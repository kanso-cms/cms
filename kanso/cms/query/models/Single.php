<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

use kanso\framework\utility\Str;

/**
 * Filter single posts.
 *
 * @author Joe J. Howard
 */
class Single extends FilterBase implements FilterInterface
{
    /**
     * The request type
     *
     * @var string
     */
    protected $requestType = 'single';

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $status    = $this->Request->fetch('query') === 'draft' && $this->Gatekeeper->isAdmin() ? 'draft' : 'published';
        $queryStr  = "post_status = {$status} : post_type = post : post_slug = {$this->postSlug()}/";
        $posts     = $this->parseQueryStr($queryStr);
        $postCount = count($posts);

        if ($postCount === 0)
        {
            return false;
        }

        $this->Query->requestType  = $this->requestType();
        $this->Query->queryStr     = $queryStr;
        $this->Query->posts        = $posts;
        $this->Query->postCount    = $postCount;

        return true;
    }

    /**
     * Returns the post slug.
     *
     * @return string
     */
    private function postSlug(): string
    {
        $slug = !empty($this->blogLocation) ? str_replace($this->blogLocation . '/', '', $this->Request->environment()->REQUEST_PATH) : $this->Request->environment()->REQUEST_PATH;

        return Str::getBeforeLastWord($slug, '/feed');
    }

}
