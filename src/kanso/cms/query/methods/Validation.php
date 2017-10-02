<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

use kanso\framework\utility\Str;


/**
 * CMS Query validation methods
 *
 * @author Joe J. Howard
 */
trait Validation
{
	/**
     * Get the currently logged in Kanso user (if any)
     *
     * @access public
     * @return \kanso\cms\wrappers\User|FALSE
     */
    public function user() 
    {
        return $this->Gatekeeper->getUser();
    }

	/**
     * Is the current user (if any) logged in
     *
     * @access public
     * @return bool
     */
    public function is_loggedIn(): bool 
    {
        return $this->Gatekeeper->isLoggedIn();
    }

    /**
     * Is the current user (if any) allowed to access the admin panel
     *
     * @access public
     * @return bool
     */
    public function user_is_admin(): bool
    {
        return $this->Gatekeeper->isAdmin();
    }

    /**
     * Get the current page type
     *
     * @access public
     * @return  string
     */
    public function the_page_type(): string
    {
        return $this->requestType;
    }

    /**
     * Is this a single request
     *
     * @access public
     * @return bool
     */
    public function is_single(): bool
    {
        return $this->requestType === 'single';
    }

    /**
     * Is this a custom post request
     *
     * @access public
     * @return bool
     */
    public function is_custom_post(): bool
    {
        return Str::getBeforeFirstChar($this->requestType, '-') === 'single' && !$this->is_single();
    }

    /**
     * Is this a request for the homepage
     *
     * @access public
     * @return bool
     */
    public function is_home(): bool
    {
        return $this->requestType === 'home';
    }

    /**
     * is this a request for the blog location ?
     *
     * @access public
     * @return bool
     */
    public function is_blog_location(): bool
    {
        return $this->requestType === 'home-page';
    }

    /**
     * Is this the first page of a paginated set of posts ?
     *
     * @access public
     * @return bool
     */
    function is_front_page(): bool
    {
       return $this->pageIndex === 0;
    }

    /**
     * Is this a static page request ?
     *
     * @access public
     * @param  string  $slug Requested page slug (optional) (default null)
     * @return bool
     */
    public function is_page($slug = null): bool
    {
        if ($slug)
        {
            $uri = strtolower(trim($this->Request->environment()->REQUEST_URI, '/'));

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

            $requestPath = trim(Str::getBeforeFirstChar($this->Request->path(), '?'), '/');
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

        return $this->requestType === 'page';
    }

    /**
     * Is this a search results request ?
     *
     * @access public
     * @return bool
     */
    public function is_search(): bool
    {
        return $this->requestType === 'search';
    }

   /**
     * Is this a tag request ?
     *
     * @access public
     * @return bool
     */
    public function is_tag(): bool
    {
        return $this->requestType === 'tag';
    }

    /**
     * Is this a category request ?
     *
     * @access public
     * @return bool
     */
    public function is_category(): bool
    {
        return $this->requestType === 'category';
    }

    /**
     * Is this an author request ?
     *
     * @access public
     * @return bool
     */
    public function is_author(): bool
    {
        return $this->requestType === 'author';
    }

    /**
     * Is this an admin request ?
     *
     * @access public
     * @return bool
     */
    public function is_admin(): bool
    {
        return  $this->requestType === 'admin';
    }

    /**
     * Is this an attachment request ?
     *
     * @access public
     * @return bool
     */
    public function is_attachment(): bool
    {
        return  $this->requestType === 'attachment';
    }

    /**
     * Is this a 404 request/response ?
     *
     * @access public
     * @return bool
     */
    public function is_not_found(): bool
    {
        return $this->Response->status()->get() === 404;
    }
}
