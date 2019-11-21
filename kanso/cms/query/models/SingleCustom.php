<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

use kanso\framework\utility\Str;

/**
 * Filter custom post types posts.
 *
 * @author Joe J. Howard
 */
class SingleCustom extends FilterBase implements FilterInterface
{
    /**
     * Post type.
     *
     * @var string
     */
    private $requestType;

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $type      = Str::getAfterFirstChar($this->requestType, '-');
        $status    = $this->Request->fetch('query') === 'draft' && $this->Gatekeeper->isAdmin() ? 'draft' : 'published';
        $queryStr  = "post_status = {$status} : post_type = {$type} : post_slug = {$this->postSlug()}/";
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
     * {@inheritdoc}
     */
    public function requestType(): string
    {
        return $this->requestType;
    }

    /**
     * Set post type.
     */
    public function setRequestType(string $type): void
    {
        $this->requestType = $type;
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
