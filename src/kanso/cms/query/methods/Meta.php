<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

use kanso\framework\utility\Str;

/**
 * CMS Query meta methods
 *
 * @author Joe J. Howard
 */
trait Meta
{
    /**
     * Get the website title from the config
     *
     * @access public
     * @return string
     */
    public function website_title(): string
    {
        return $this->Config->get('cms.site_title');
    }

    /**
     * Get the website description from the config
     *
     * @access public
     * @return string
     */
    public function website_description(): string
    {
        return $this->Config->get('cms.site_description');
    }

    /**
     * Get the website's domain name (e.g "example.com")
     *
     * @access public
     * @return string
     */
    public function domain_name(): string
    {
        return $this->Request->environment()->DOMAIN_NAME;
    }

    /**
     * Get the meta description to display in the website's head
     *
     * @access public
     * @return string
     */
    public function the_meta_description(): string
    {
        if ($this->is_not_found())
        {
            return 'The page you are looking for could not be found.';
        }
        
        $description = $this->website_description();
        
        if ($this->is_single() || $this->is_page() || $this->is_custom_post())
        {
            $description = $this->post->excerpt;
        }
        else if ($this->is_search())
        {
            $description = 'Search Results for: '.$this->search_query().' - '.$this->website_title();
        }

        return Str::reduce($description, 180);
    }

    /**
     * Get the meta title to display in the website's head
     *
     * @access public
     * @return string
     */
    public function the_meta_title(): string
    {
        $uri        = explode("/", trim($this->Request->environment()->REQUEST_URI, '/'));
        $titleBase  = $this->website_title();
        $titlePage  = $this->pageIndex > 0 ? 'Page '.($this->pageIndex+1).' | ' : '';
        $titleTitle = '';

        if ($this->is_not_found())
        {
            return 'Page Not Found';
        }

        if ($this->is_single() || $this->is_page() || $this->is_custom_post())
        {
            if ($this->have_posts())
            {
                $titleTitle = $this->post->title.' | ';
            }
        }
        else if ($this->is_tag() || $this->is_category() || $this->is_author())
        {
            $titleTitle = $this->the_taxonomy()->name.' | ';
        }
        else if ($this->is_search())
        {
            $titleTitle = 'Search Results | ';
        }

        return  $titleTitle.$titlePage.$titleBase;
    }

    /**
     * Get the canonical URL to display in the website's head
     *
     * @access public
     * @return string
     */
    public function the_canonical_url(): string
    {
        $page = $this->pageIndex;
        $env  = $this->Request->environment()->asArray();
        $base = $env['HTTP_HOST'];
        $uri  = explode("/", trim($env['REQUEST_URI'], '/'));
        $slug = '';

        if (!$this->have_posts() || $this->is_not_found())
        {
            return $env['HTTP_HOST'].$env['REQUEST_URI'];
        }

        if ($this->is_home() || $this->is_single() || $this->is_tag() || $this->is_category() || $this->is_author() || $this->is_blog_location() )
        {
            $prefix = !empty($this->blog_location()) ? '/'.$this->blog_location() : '';
            $base   .= $prefix;
        }

        if ($this->is_single() || $this->is_page() || $this->is_custom_post())
        {
            $slug = $this->post->slug;
        }
        if ($this->is_home() )
        {
            $slug = $page > 1 ? 'page/'.$page.'/' : '';
        }
        else if ($this->is_blog_location())
        {
            $slug = $page > 1 ? $base.'page/'.$page.'/' : '';
        }
        else if ($this->is_tag() || $this->is_category() || $this->is_author() )
        {
            $taxonomy   = $this->is_tag() ? 'tag' : 'author';
            $taxonomy   = $this->is_category() ? 'category' : $taxonomy;
            $titleTitle = $this->the_taxonomy()->name.' | ';
            $slug       = $page > 1 ? $base.$taxonomy.'/'.$this->taxonomySlug.'/page/'.$page.'/' : $base.$taxonomy.'/'.$this->taxonomySlug.'/';
        }
        else if ($this->is_search())
        {
            $slug = $page > 1 ? $uri[0].'/'.$uri[1].'/page/'.$page.'/' : $uri[0].'/'.$uri[1].'/';
        }
        else
        {
            return $env['HTTP_HOST'].$env['REQUEST_URI'];
        }

        return "$base/$slug";
    }

    /**
     * Get the title of the next page or post. 
     * Works on single, home, author, tag, category requests
     *
     * @access public
     * @return string|false
     */
    public function the_previous_page_title()
    {
        $prev_page = $this->the_previous_page();
        
        if ($prev_page && isset($prev_page['title']))
        {
            return $prev_page['title'];
        }
        
        return false;
    }

    /**
     * Get the title of the next page or post. 
     * Works on single, home, author, tag, category requests
     *
     * @access public
     * @return string|false
     */
    public function the_next_page_title()
    {
        $next_page = $this->the_next_page();
        
        if ($next_page && isset($next_page['title']))
        {
            return $next_page['title'];
        
        }
        
        return false;
    }

    /**
     * Get the full URL of the next page or post. 
     * Works on single, home, author, tag, category requests
     *
     * @access public
     * @return  string|false
     */
    public function the_next_page_url()
    {
        $next_page = $this->the_next_page();
        
        if ($next_page && isset($next_page['slug']))
        {
            return $this->Request->environment()->HTTP_HOST.'/'.$next_page['slug'];
        }
        
        return false;
    }

    /**
     * Get the full URL of the previous page or post. 
     * Works on single, home, author, tag, category requests
     *
     * @access public
     * @return  string|false
     */
    public function the_previous_page_url()
    {
        $prev_page = $this->the_previous_page();

        if ($prev_page && isset($prev_page['slug']))
        {
            return $this->Request->environment()->HTTP_HOST.'/'.$prev_page['slug'];
        }

        return false;
    }

    /**
     * Gets an array for the previous page or post returning its title and slug. 
     * Works on single, home, author, tag, category requests
     *
     * @access public
     * @return array|false
     */
    public function the_previous_page()
    {
        # Not found don't bother
        if ($this->is_not_found())
        {
            return false;
        }

        # Get from the cache
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());
       
        # There are only next/prev pages for single, tags, category, author, and homepage 
        if (!in_array($this->requestType, ['single', 'home', 'home-page', 'tag', 'category', 'author']) && !$this->is_custom_post())
        {
            return $this->cache->set($key, false);
        }
        
        # Load from cache if we can
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }
        
        # If this is a single or custom post just find the next post
        if ($this->is_single() || $this->is_custom_post())
        {
            return $this->cache->set($key, $this->findPrevPost($this->post));
        }

        # This must now be a paginated page - tag, category, author or homepage listing
        # Get the current page + posts per page and check if there is a page before that
        if ($this->pageIndex > 0 )
        {
            $perPage  = $this->Config->get('cms.posts_per_page');
            $page     = $this->pageIndex - 1;
            $offset   = $page * $perPage;
            $limit    = 1;
            $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->queryStr);

            $posts    = $this->queryParser->parseQuery($queryStr);
        }
        else
        {
            $posts = [];
        }

        if (!empty($posts))
        {
            $prevpage   = $this->pageIndex;
            $uri        = explode("/", trim($this->Request->environment()->REQUEST_URI, '/'));
            $titleBase  = $this->website_title();
            $titlePage  = $prevpage > 1 ? 'Page '.$prevpage.' | ' : '';
            $titleTitle = '';
            $base       = !empty($this->blog_location()) && ($this->is_tag() || $this->is_category() || $this->is_author() || $this->is_home() || $this->is_blog_location()) ? $this->blog_location().'/' : '';

            if ($this->is_home())
            {
                if (!empty($this->blog_location()))
                {
                    return false;
                }

                $slug = $prevpage > 1 ? 'page/'.$prevpage.'/' : '';
            }
            else if ($this->is_blog_location())
            {
                $titleTitle = 'Blog | ';
                $slug       = $prevpage > 1 ? $base.'page/'.$prevpage.'/' : $base;
            }
            else if ($this->is_tag() || $this->is_category() || $this->is_author() )
            {
                $taxonomy   = $this->is_tag() ? 'tag' : 'author';
                $taxonomy   = $this->is_category() ? 'category' : $taxonomy;
                $titleTitle = $this->the_taxonomy()->name.' | ';
                $slug       = $prevpage > 1 ? $base.$taxonomy.'/'.$this->taxonomySlug.'/page/'.$prevpage.'/' : $base.$taxonomy.'/'.$this->taxonomySlug.'/';
            }
            else if ($this->is_search())
            {
                $titleTitle = 'Search Results | ';
                $slug       =  $prevpage > 1 ? $uri[0].'/'.$uri[1].'/page/'.$prevpage.'/' : $uri[0].'/'.$uri[1].'/';
            }
            return $this->cache->set($key, [
                'title' => $titleTitle.$titlePage.$titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->cache->set($key, false);
    }

    /**
     * Gets an array for the next page returning its title and slug. 
     * Works on single, home, author, tag, category requests
     *
     * @access public
     * @return array|false
     */
    public function the_next_page()
    {
        # Not found don't bother
        if ($this->is_not_found())
        {
            return false;
        }

        # Get for the cache
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        # There are only next/prev pages for single, tags, category, author, and homepage 
        if (!in_array($this->requestType, ['single', 'home', 'home-page', 'tag', 'category', 'author']) && !$this->is_custom_post())
        {
            return $this->cache->set($key, false);
        }
        
        # Load from cache if we can 
        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        # If this is a single or custom post just find the next post
        if ($this->is_single() || $this->is_custom_post())
        {
            return $this->cache->set($key, $this->findNextPost($this->post));
        }

        # This must now be a paginated page - tag, category, author or homepage listing
        # Get the current page + posts per page and check if there is a page after that
        $perPage  = $this->Config->get('cms.posts_per_page');
        $page     = $this->pageIndex + 1;
        $offset   = $page * $perPage;
        $limit    = 1;
        $queryStr = preg_replace('/limit.+/', "limit = $offset, $limit", $this->queryStr);
        $posts    = $this->queryParser->parseQuery($queryStr);
        $base     = !empty($this->blog_location()) && ($this->is_tag() || $this->is_category() || $this->is_author() || $this->is_home() || $this->is_blog_location()) ? $this->blog_location().'/' : '';

        if (!empty($posts))
        {
            $nextPage   = $this->pageIndex + 2;
            $uri        = explode("/", trim($this->Request->environment()->REQUEST_URI, '/'));
            $titleBase  = $this->website_title();
            $titlePage  = $nextPage > 1 ? 'Page '.$nextPage.' | ' : '';
            $titleTitle = '';

            if ($this->is_home())
            {
                if (!empty($this->blog_location()))
                {
                    return false;
                }

                $slug = 'page/'.$nextPage.'/';
            }
            else if ($this->is_blog_location())
            {
                $titleTitle = 'Blog | ';
                $slug       = $base.'page/'.$nextPage.'/';
            }
            else if ($this->is_tag() || $this->is_category() || $this->is_author() )
            {
                $taxonomy   = $this->is_tag() ? 'tag' : 'author';
                $taxonomy   = $this->is_category() ? 'category' : $taxonomy;
                $titleTitle = $this->the_taxonomy()->name.' | ';
                $titleTitle = $this->the_taxonomy()->name. ' | ';
                $slug       = $base.$taxonomy.'/'.$this->taxonomySlug.'/page/'.$nextPage.'/';
            }
            else if ($this->is_search())
            {
                $titleTitle = 'Search Results | ';
                $slug       = $uri[0].'/'.$uri[1].'/page/'.$nextPage.'/';
            }
            return $this->cache->set($key, [
                'title' => $titleTitle.$titlePage.$titleBase,
                'slug'  => $slug,
            ]);
        }

        return $this->cache->set($key, false);
    }

    /**
     * Find the next post (used internally)
     *
     * @access private
     * @param  \Kanso\cms\wrappers\Post|null     
     * @return  array|false
     */
    private function findNextPost($post)
    {
        if (!$post)
        {
            return false;
        }
        
        $next = $this->SQL->SELECT('id')->FROM('posts')->WHERE('created', '>=', $post->created)->AND_WHERE('type', '=', $post->type)->AND_WHERE('status', '=', 'published')->ORDER_BY('created', 'ASC')->FIND_ALL();
        
        if (!empty($next))
        {
            $next = array_values($next);
            
            foreach ($next as $i => $prevPost)
            {
                if ((int)$prevPost['id'] === (int)$post->id)
                {
                    if (isset($next[$i+1]))
                    {
                        return $this->SQL->SELECT('*')->FROM('posts')->AND_WHERE('type', '=', $post->type)->WHERE('id', '=', $next[$i+1]['id'])->ROW();
                    }
                }
            }
        }

        return false;
    }

    /**
     * Find the previous post (used internally)
     *
     * @access private
     * @param  \Kanso\cms\wrappers\Post|null     
     * @return  array|false
     */
    private function findPrevPost($post)
    {
        if (!$post)
        {
            return false;
        }
        
        $next = $this->SQL->SELECT('id')->FROM('posts')->WHERE('created', '<=', $post->created)->AND_WHERE('type', '=', $post->type)->AND_WHERE('status', '=', 'published')->ORDER_BY('created', 'DESC')->FIND_ALL();
        
        if (!empty($next))
        {
            $next = array_values($next);
            
            foreach ($next as $i => $prevPost)
            {
                if ((int)$prevPost['id'] === (int)$post->id)
                {
                    if (isset($next[$i+1]))
                    {
                        return $this->SQL->SELECT('*')->FROM('posts')->AND_WHERE('type', '=', $post->type)->WHERE('id', '=', $next[$i+1]['id'])->ROW();
                    }
                }
            }
        }

        return false;
    }
}
