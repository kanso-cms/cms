<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

/**
 * Filter single posts.
 *
 * @author Joe J. Howard
 */
class Single extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        // Blog prefix
        $blogPrefix = $this->container->Config->get('cms.blog_location');

        $slug = !empty($blogPrefix) ? str_replace($blogPrefix . '/', '', $this->container->Request->environment()->REQUEST_PATH) : $this->container->Request->environment()->REQUEST_PATH;

        $slug = Str::getBeforeLastWord($slug, '/feed');

        $postType = $this->requestType === 'single' ? 'post' : Str::getAfterFirstChar($this->requestType, '-');

        $this->parent->requestType = $this->requestType;

        // Not logged in as admin drafts
        if ($this->container->Request->fetch('query') === 'draft' && !$this->container->Gatekeeper->isAdmin())
        {
            return false;
        }

        $this->parent->queryStr  = 'post_status = published : post_type = ' . $postType . ' : post_slug = ' . $slug . '/';
        $this->parent->posts     = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);
        $this->parent->postCount = count($this->parent->posts);

        if ($this->parent->postCount === 0)
        {
            $this->container->Response->status()->set(404);

            return false;
        }

        return true;
    }
}
