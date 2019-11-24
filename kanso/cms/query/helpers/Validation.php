<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\utility\Str;

/**
 * CMS Query validation methods.
 *
 * @author Joe J. Howard
 */
class Validation extends Helper
{
    /**
     * Get the currently logged in Kanso user (if any).
     *
     * @return \kanso\cms\wrappers\User|false
     */
    public function user()
    {
        return $this->container->Gatekeeper->getUser();
    }

    /**
     * Is the current user (if any) logged in.
     *
     * @return bool
     */
    public function is_loggedIn(): bool
    {
        return $this->container->Gatekeeper->isLoggedIn();
    }

    /**
     * Is the current user (if any) allowed to access the admin panel.
     *
     * @return bool
     */
    public function user_is_admin(): bool
    {
        return $this->container->Gatekeeper->isAdmin();
    }

    /**
     * Get the current page type.
     *
     * @return string
     */
    public function the_page_type(): string
    {
        if (empty($this->parent->requestType))
        {
            return '';
        }

        return $this->parent->requestType;
    }

    /**
     * Is this a single request.
     *
     * @return bool
     */
    public function is_single(): bool
    {
        return $this->parent->requestType === 'single';
    }

    /**
     * Is this a custom post request.
     *
     * @return bool
     */
    public function is_custom_post(): bool
    {
        if (empty($this->parent->requestType))
        {
            return false;
        }

        return Str::getBeforeFirstChar($this->parent->requestType, '-') === 'single' && !$this->parent->is_single();
    }

    /**
     * Is this a request for the homepage.
     *
     * @return bool
     */
    public function is_home(): bool
    {
        return $this->parent->requestType === 'home';
    }

    /**
     * is this a request for the blog location ?
     *
     * @return bool
     */
    public function is_blog_location(): bool
    {
        return $this->parent->requestType === 'home-page';
    }

    /**
     * Is this the first page of a paginated set of posts ?
     *
     * @return bool
     */
    public function is_front_page(): bool
    {
       return $this->parent->pageIndex === 0;
    }

    /**
     * Is this a static page request ?
     *
     * @param  string $slug Requested page slug (optional) (default null)
     * @return bool
     */
    public function is_page($slug = null): bool
    {
        if ($slug)
        {
            $uri = strtolower($this->container->Request->environment()->REQUEST_PATH);

            $slug = strtolower(trim($slug, '/'));

            if ($slug === $uri)
            {
                return true;
            }

            $patterns =
            [
                ':any'      => '[^/]+',
                ':num'      => '[0-9]+',
                ':all'      => '.*',
                ':year'     => '\d{4}',
                ':month'    => '0?[1-9]|1[012]',
                ':day'      => '0[1-9]|[12]\d|3[01]',
                ':hour'     => '0?[1-9]|1[012]',
                ':minute'   => '[0-5]?\d',
                ':second'   => '[0-5]?\d',
                ':postname' => '[a-z0-9 -]+',
                ':category' => '[a-z0-9 -]+',
                ':author'   => '[a-z0-9 -]+',
            ];

            $requestPath = $uri;
            $searches    = array_keys($patterns);
            $replaces    = array_values($patterns);
            $route       = $slug;

            if (strpos($route, ':') !== false)
            {
                $route = str_replace($searches, $replaces, $route);
            }

            if (preg_match('#^' . $route . '$#', $requestPath, $matches))
            {
                return true;
            }

            return false;
        }

        return $this->parent->requestType === 'page';
    }

    /**
     * Is this a search results request ?
     *
     * @return bool
     */
    public function is_search(): bool
    {
        return $this->parent->requestType === 'search';
    }

    /**
     * Is this a tag request ?
     *
     * @return bool
     */
    public function is_tag(): bool
    {
        return $this->parent->requestType === 'tag';
    }

    /**
     * Is this a category request ?
     *
     * @return bool
     */
    public function is_category(): bool
    {
        return $this->parent->requestType === 'category';
    }

    /**
     * Is this an author request ?
     *
     * @return bool
     */
    public function is_author(): bool
    {
        return $this->parent->requestType === 'author';
    }

    /**
     * Is this an admin request ?
     *
     * @return bool
     */
    public function is_admin(): bool
    {
        return $this->parent->is_page('/admin/(:all)/');
    }

    /**
     * Is this an attachment request ?
     *
     * @return bool
     */
    public function is_attachment(): bool
    {
        return  $this->parent->requestType === 'attachment';
    }

    /**
     * Is this a 404 request/response ?
     *
     * @return bool
     */
    public function is_not_found(): bool
    {
        return $this->container->Response->status()->get() === 404;
    }
}
