<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\utility\Str;

/**
 * CMS Query meta methods.
 *
 * @author Joe J. Howard
 */
class Meta extends Helper
{
    /**
     * Get the website title from the config.
     *
     * @return string
     */
    public function website_title(): string
    {
        return $this->container->Config->get('cms.site_title');
    }

    /**
     * Get the website description from the config.
     *
     * @return string
     */
    public function website_description(): string
    {
        return $this->container->Config->get('cms.site_description');
    }

    /**
     * Get the website's domain name (e.g "example.com").
     *
     * @return string
     */
    public function domain_name(): string
    {
        return $this->container->Request->environment()->DOMAIN_NAME;
    }

    /**
     * Get the meta description to display in the website's head.
     *
     * @return string
     */
    public function the_meta_description(): string
    {
        if ($this->parent->is_not_found())
        {
            return 'The page you are looking for could not be found.';
        }

        $description = $this->parent->website_description();

        if ($this->parent->is_single() || $this->parent->is_page() || $this->parent->is_custom_post())
        {
            $description = $this->parent->post->excerpt;
        }
        elseif ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author())
        {
            $description = $this->parent->the_taxonomy()->description;
        }
        elseif ($this->parent->is_search())
        {
            $description = 'Search Results for: ' . $this->parent->search_query() . ' - ' . $this->parent->website_title();
        }

        if (!$description)
        {
            $description = '';
        }

        return Str::reduce($description, 300);
    }

    /**
     * Get the meta title to display in the website's head.
     *
     * @return string
     */
    public function the_meta_title(): string
    {
        $uri        = explode('/', $this->container->Request->environment()->REQUEST_PATH);
        $titleBase  = $this->parent->website_title();
        $titlePage  = $this->parent->pageIndex > 0 ? 'Page ' . ($this->parent->pageIndex+1) . ' | ' : '';
        $titleTitle = '';

        if ($this->parent->is_not_found())
        {
            return 'Page Not Found';
        }

        if ($this->parent->is_single() || $this->parent->is_page() || $this->parent->is_custom_post())
        {
            if ($this->parent->have_posts())
            {
                $titleTitle = $this->parent->post->title . ' | ';
            }
        }
        elseif ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author())
        {
            $titleTitle = $this->parent->the_taxonomy()->name . ' | ';
        }
        elseif ($this->parent->is_search())
        {
            $titleTitle = 'Search Results | ';
        }

        return  $titleTitle . $titlePage . $titleBase;
    }

    /**
     * Get the canonical URL to display in the website's head.
     *
     * @return string
     */
    public function the_canonical_url(): string
    {
        $urlParts = array_filter(explode('/', $this->container->Request->environment()->REQUEST_PATH));
        $last     = isset($urlParts[0]) ? array_values(array_slice($urlParts, -1))[0] : false;

        if (!$last || is_home())
        {
            return $this->parent->home_url();
        }

        if ($last === 'rss' || $last === 'rdf' || $last == 'atom')
        {
            array_pop($urlParts);
            array_pop($urlParts);
        }
        elseif ($last === 'feed')
        {
            array_pop($urlParts);
        }

        return $this->container->Request->environment()->HTTP_HOST . '/' . implode('/', $urlParts) . '/';
    }

    /**
     * Get the title of the next page or post.
     * Works on single, home, author, tag, category requests.
     *
     * @return string|false
     */
    public function the_previous_page_title()
    {
        $prev_page = $this->parent->the_previous_page();

        if ($prev_page && isset($prev_page['title']))
        {
            return $prev_page['title'];
        }

        return false;
    }

    /**
     * Get the title of the next page or post.
     * Works on single, home, author, tag, category requests.
     *
     * @return string|false
     */
    public function the_next_page_title()
    {
        $next_page = $this->parent->the_next_page();

        if ($next_page && isset($next_page['title']))
        {
            return $next_page['title'];

        }

        return false;
    }

    /**
     * Get the full URL of the next page or post.
     * Works on single, home, author, tag, category requests.
     *
     * @return string|false
     */
    public function the_next_page_url()
    {
        $next_page = $this->parent->the_next_page();

        if ($next_page && isset($next_page['slug']))
        {
            return $this->container->Request->environment()->HTTP_HOST . '/' . $next_page['slug'];
        }

        return false;
    }

    /**
     * Get the full URL of the previous page or post.
     * Works on single, home, author, tag, category requests.
     *
     * @return string|false
     */
    public function the_previous_page_url()
    {
        $prev_page = $this->parent->the_previous_page();

        if ($prev_page && isset($prev_page['slug']))
        {
            return $this->container->Request->environment()->HTTP_HOST . '/' . $prev_page['slug'];
        }

        return false;
    }

    /**
     * Gets an array for the previous page or post returning its title and slug.
     * Works on single, home, author, tag, category requests.
     *
     * @return array|false
     */
    public function the_previous_page()
    {
        // Not found don't bother
        if ($this->parent->is_not_found())
        {
            return false;
        }

        // Get from the cache
        $key = $this->parent->helper('cache')->key(__FUNCTION__, func_get_args(), func_num_args());

        // There are only next/prev pages for single, tags, category, author, and homepage
        if (!in_array($this->parent->requestType, ['single', 'home', 'home-page', 'tag', 'category', 'author']) && !$this->parent->is_custom_post())
        {
            return $this->parent->helper('cache')->set($key, false);
        }

        // Load from cache if we can
        if ($this->parent->helper('cache')->has($key))
        {
            return $this->parent->helper('cache')->get($key);
        }

        // If this is a single or custom post just find the next post
        if ($this->parent->is_single() || $this->parent->is_custom_post())
        {
            return $this->parent->helper('cache')->set($key, $this->findPrevPost($this->parent->post));
        }

        // This must now be a paginated page - tag, category, author or homepage listing
        // Get the current page + posts per page and check if there is a page before that
        if ($this->parent->pageIndex > 0)
        {
            $perPage  = $this->container->Config->get('cms.posts_per_page');
            $page     = $this->parent->pageIndex - 1;
            $offset   = $page * $perPage;
            $limit    = 1;
            $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->parent->queryStr);

            $posts    = $this->parent->helper('parser')->parseQuery($queryStr);
        }
        else
        {
            $posts = [];
        }

        if (!empty($posts))
        {
            $prevpage   = $this->parent->pageIndex;
            $uri        = explode('/', $this->container->Request->environment()->REQUEST_PATH);

            $titleBase  = $this->parent->website_title();
            $titlePage  = $prevpage > 1 ? 'Page ' . $prevpage . ' | ' : '';
            $titleTitle = '';
            $base       = !empty($this->parent->blog_location()) && ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author() || $this->parent->is_home() || $this->parent->is_blog_location()) ? $this->parent->blog_location() . '/' : '';

            if ($this->parent->is_home())
            {
                if (!empty($this->parent->blog_location()))
                {
                    return false;
                }

                $slug = $prevpage > 1 ? 'page/' . $prevpage . '/' : '';
            }
            elseif ($this->parent->is_blog_location())
            {
                $titleTitle = 'Blog | ';
                $slug       = $prevpage > 1 ? $base . 'page/' . $prevpage . '/' : $base;
            }
            elseif ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author())
            {
                $taxonomy   = $this->parent->is_tag() ? 'tag' : 'author';
                $taxonomy   = $this->parent->is_category() ? 'category' : $taxonomy;
                $titleTitle = $this->parent->the_taxonomy()->name . ' | ';
                $slug       = $prevpage > 1 ? $base . $taxonomy . '/' . $this->parent->taxonomySlug . '/page/' . $prevpage . '/' : $base . $taxonomy . '/' . $this->parent->taxonomySlug . '/';
            }
            elseif ($this->parent->is_search())
            {
                $titleTitle = 'Search Results | ';
                $slug       =  $prevpage > 1 ? $uri[0] . '/' . $uri[1] . '/page/' . $prevpage . '/' : $uri[0] . '/' . $uri[1] . '/';
            }
            return $this->parent->helper('cache')->set($key, [
                'title' => $titleTitle . $titlePage . $titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->parent->helper('cache')->set($key, false);
    }

    /**
     * Gets an array for the next page returning its title and slug.
     * Works on single, home, author, tag, category requests.
     *
     * @return array|false
     */
    public function the_next_page()
    {
        // Not found don't bother
        if ($this->parent->is_not_found())
        {
            return false;
        }

        // Get for the cache
        $key = $this->parent->helper('cache')->key(__FUNCTION__, func_get_args(), func_num_args());

        // There are only next/prev pages for single, tags, category, author, and homepage
        if (!in_array($this->parent->requestType, ['single', 'home', 'home-page', 'tag', 'category', 'author']) && !$this->parent->is_custom_post())
        {
            return $this->parent->helper('cache')->set($key, false);
        }

        // Load from cache if we can
        if ($this->parent->helper('cache')->has($key))
        {
            return $this->parent->helper('cache')->get($key);
        }

        // If this is a single or custom post just find the next post
        if ($this->parent->is_single() || $this->parent->is_custom_post())
        {
            return $this->parent->helper('cache')->set($key, $this->findNextPost($this->parent->post));
        }

        // This must now be a paginated page - tag, category, author or homepage listing
        // Get the current page + posts per page and check if there is a page after that
        $perPage  = $this->container->Config->get('cms.posts_per_page');
        $page     = $this->parent->pageIndex + 1;
        $offset   = $page * $perPage;
        $limit    = 1;
        $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->parent->queryStr);
        $posts    = $this->parent->helper('parser')->parseQuery($queryStr);
        $base     = !empty($this->parent->blog_location()) && ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author() || $this->parent->is_home() || $this->parent->is_blog_location()) ? $this->parent->blog_location() . '/' : '';

        if (!empty($posts))
        {
            $nextPage   = $this->parent->pageIndex + 2;
            $uri        = explode('/', $this->container->Request->environment()->REQUEST_PATH);
            $titleBase  = $this->parent->website_title();
            $titlePage  = $nextPage > 1 ? 'Page ' . $nextPage . ' | ' : '';
            $titleTitle = '';

            if ($this->parent->is_home())
            {
                if (!empty($this->parent->blog_location()))
                {
                    return false;
                }

                $slug = 'page/' . $nextPage . '/';
            }
            elseif ($this->parent->is_blog_location())
            {
                $titleTitle = 'Blog | ';
                $slug       = $base . 'page/' . $nextPage . '/';
            }
            elseif ($this->parent->is_tag() || $this->parent->is_category() || $this->parent->is_author())
            {
                $taxonomy   = $this->parent->is_tag() ? 'tag' : 'author';
                $taxonomy   = $this->parent->is_category() ? 'category' : $taxonomy;
                $titleTitle = $this->parent->the_taxonomy()->name . ' | ';
                $titleTitle = $this->parent->the_taxonomy()->name . ' | ';
                $slug       = $base . $taxonomy . '/' . $this->parent->taxonomySlug . '/page/' . $nextPage . '/';
            }
            elseif ($this->parent->is_search())
            {
                $titleTitle = 'Search Results | ';
                $slug       = $uri[0] . '/' . $uri[1] . '/page/' . $nextPage . '/';
            }
            return $this->parent->helper('cache')->set($key, [
                'title' => $titleTitle . $titlePage . $titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->parent->helper('cache')->set($key, false);
    }

    /**
     * Find the next post (used internally).
     *
     * @param  \Kanso\cms\wrappers\Post|null $post Current post (if it exists)
     * @return array|false
     */
    private function findNextPost($post)
    {
        if (!$post)
        {
            return false;
        }

        $next = $this->sql()->SELECT('id')->FROM('posts')->WHERE('created', '>=', $post->created)->AND_WHERE('type', '=', $post->type)->AND_WHERE('status', '=', 'published')->ORDER_BY('created', 'ASC')->FIND_ALL();

        if (!empty($next))
        {
            $next = array_values($next);

            foreach ($next as $i => $prevPost)
            {
                if ((int) $prevPost['id'] === (int) $post->id)
                {
                    if (isset($next[$i+1]))
                    {
                        return $this->sql()->SELECT('*')->FROM('posts')->AND_WHERE('type', '=', $post->type)->WHERE('id', '=', $next[$i+1]['id'])->ROW();
                    }
                }
            }
        }

        return false;
    }

    /**
     * Find the previous post (used internally).
     *
     * @param  \Kanso\cms\wrappers\Post|null $post Current post (if it exists)
     * @return array|false
     */
    private function findPrevPost($post)
    {
        if (!$post)
        {
            return false;
        }

        $next = $this->sql()->SELECT('id')->FROM('posts')->WHERE('created', '<=', $post->created)->AND_WHERE('type', '=', $post->type)->AND_WHERE('status', '=', 'published')->ORDER_BY('created', 'DESC')->FIND_ALL();

        if (!empty($next))
        {
            $next = array_values($next);

            foreach ($next as $i => $prevPost)
            {
                if ((int) $prevPost['id'] === (int) $post->id)
                {
                    if (isset($next[$i+1]))
                    {
                        return $this->sql()->SELECT('*')->FROM('posts')->AND_WHERE('type', '=', $post->type)->WHERE('id', '=', $next[$i+1]['id'])->ROW();
                    }
                }
            }
        }

        return false;
    }
}
