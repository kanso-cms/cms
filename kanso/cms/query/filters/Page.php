<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

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
        $slug = Str::getBeforeLastWord($this->container->Request->environment()->REQUEST_PATH, '/feed');
        $this->parent->requestType = 'page';

        // Not logged in as admin drafts
        if ($this->container->Request->fetch('query') === 'draft' && !$this->container->Gatekeeper->isAdmin())
        {
            return false;
        }

        $this->parent->queryStr   = 'post_status = published : post_type = page : post_slug = ' . $slug . '/';
        $this->parent->posts      = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);
        $this->parent->postCount  = count($this->parent->posts);

        if ($this->parent->postCount === 0)
        {
            $this->container->Response->status()->set(404);

            return false;
        }

        return true;

    }
}
