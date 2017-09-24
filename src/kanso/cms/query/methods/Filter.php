<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

use kanso\framework\utility\Str;

/**
 * CMS Query filter methods
 *
 * @author Joe J. Howard
 */
trait Filter
{
    /**
     * Reset the internal properties to default
     *
     * @access public
     */
    public function reset()
    {
        $this->pageIndex    = 0;
        $this->postIndex    = -1;
        $this->postCount    = 0;
        $this->posts        = [];
        $this->requestType  = NULL;
        $this->queryStr     = NULL;
        $this->post         = NULL;
        $this->taxonomySlug = NULL;
        $this->searchQuery  = NULL;
    }

	/**
     * Fetch and set the currently requested page
     *
     * @access private
     */
	private function fetchPageIndex()
	{
		$this->pageIndex = $this->Request->fetch('page');
        
        $this->pageIndex = $this->pageIndex === 1 || $this->pageIndex === 0 ? 0 : $this->pageIndex-1;
	}

	/**
     * Apply a query for a custom string
     *
     * @access public
     * @param  string  $queryStr Query string to parse
     */
    private function applyQuery(string $queryStr)
    {
        $this->reset();

        $this->queryStr = trim($queryStr);

        $this->posts = $this->queryParser->parseQuery($this->queryStr);

        $this->postCount = count($this->posts);

        $this->requestType = 'custom';

        if (isset($this->posts[0]))
        {
            $this->post = $this->posts[0];
        }
    }

    /**
     * Filter the posts by the request type
     *
     * Note this method is used from the router/CMS core to filter posts based
     * on the matched route.
     *
     * @access public
     * @param  string $requestType The requested page type
     */
    public function filterPosts(string $requestType)
    {        
        # Reset the internal properties
        $this->reset();

        # Reset the response to 200
        $this->Response->status()->set(200);

        # Reset the page index
        $this->fetchPageIndex();
    
        # Filter and paginate the posts based on the request type
        if ($requestType === 'home' || $requestType === 'home-page')
        {
            if (!$this->filterHome($requestType))
            {
                return false;
            }
        }
        else if ($requestType === 'tag')
        {
            if (!$this->filterTag())
            {
                return false;
            }
        }
        else if ($requestType === 'category1' || $requestType === 'category2' || $requestType === 'category3')
        {
            if (!$this->filterCategory($requestType))
            {
                return false;
            }            
        } 
        else if ($requestType === 'author')
        {
            if (!$this->filterAuthor())
            {
                return false;
            }            
        }
        else if ($requestType === 'single' || Str::getBeforeFirstChar($requestType, '-') === 'single')
        {
            if (!$this->filterSingle($requestType))
            {
                return false;
            }
        }
        else if ($requestType === 'page')
        {
            if (!$this->filterPage())
            {
                return false;
            }
        }
        else if ($requestType === 'search')
        {
            if (!$this->filterSearch())
            {
                return false;
            }
        }
        else if ($requestType === 'attachment')
        {
            if (!$this->filterAttachment())
            {
                return false;
            }
        } 

        # Set the_post so we're looking at the first item
        if (isset($this->posts[0]))
        {
            $this->post = $this->posts[0];
        }
    }

    /**
     * Filter the posts based on a category request
     *
     * @access private
     * @param  string  $requestType The incoming request type ('home'|'home-blog')
     * @return bool 
     */
    private function filterHome(string $requestType): bool
    {
        $perPage   = $this->Config->get('cms.posts_per_page');
        $offset    = $this->pageIndex * $perPage;

        $this->requestType = $requestType;
        $this->queryStr    = "post_status = published : post_type = post : orderBy = post_created, DESC : limit = $offset, $perPage";
        $this->posts       = $this->queryParser->parseQuery($this->queryStr);
        $this->postCount   = count($this->posts);

        if ($this->postCount === 0)
        {
            $this->Response->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on a single request
     *
     * @access private
     * @param  string  $requestType The incoming request type ('single'|'$custom-single')
     * @return bool 
     */
    private function filterSingle(string $requestType): bool
    {
        $blogPrefix = $this->Config->get('cms.blog_location');
        $uri        = trim($this->container->Request->environment()->REQUEST_URI, '/');
        $postType   = $requestType === 'single' ? 'post' : Str::getAfterFirstChar($requestType, '-'); 

        $this->requestType = $requestType;
        
        if ($this->Request->fetch('query') === 'draft' && $this->Gatekeeper->isAdmin())
        {
            $uri = ltrim(str_replace('?draft', '', $uri), '/');
            $uri = !empty($blogPrefix) ? str_replace($blogPrefix.'/', '', $uri) : $uri;
            $this->queryStr  = 'post_status = draft : post_type = '.$postType.' : post_slug = '.$uri.'/';
            $this->posts     = $this->queryParser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
        }
        else
        {
            $uri       = Str::getBeforeLastWord($uri, '/feed');
            $uri       = ltrim($uri, '/');
            $uri       = !empty($blogPrefix) ? str_replace($blogPrefix.'/', '', $uri) : $uri;
            $this->queryStr  = 'post_status = published : post_type = '.$postType.' : post_slug = '.$uri.'/';
            $this->posts     = $this->queryParser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
        }

        if ($this->postCount === 0)
        {
            $this->Response->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on a page request
     *
     * @access private
     * @return bool 
     */
    private function filterPage(): bool
    {
        $uri = trim($this->container->Request->environment()->REQUEST_URI, '/');
        $this->requestType = 'page';

        if ($this->Request->fetch('query') === 'draft' && $this->Gatekeeper->isAdmin())
        {
            $uri             = ltrim(str_replace('?draft', '', $uri), '/');
            $this->queryStr  = 'post_status = draft : post_type = page : post_slug = '.$uri.'/';
            $this->posts     = $this->queryParser->parseQuery($this->queryStr);
            $this->postCount = count($this->posts);
        }
        else
        {
            $uri = Str::getBeforeLastWord($uri, '/feed');
            $uri = Str::getBeforeLastChar($uri, '?');
            $uri = trim($uri, '/');
            $this->queryStr   = 'post_status = published : post_type = page : post_slug = '.$uri.'/';
            $this->posts      = $this->queryParser->parseQuery($this->queryStr);
            $this->postCount  = count($this->posts);
        }

        if ($this->postCount === 0)
        {
            $this->Response->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on an category request
     *
     * @access private
     * @return bool 
     */
    private function filterCategory(string $requestType): bool
    {
        $blogPrefix   = $this->Config->get('cms.blog_location');
        $perPage      = $this->Config->get('cms.posts_per_page');
        $offset       = $this->pageIndex * $perPage;
        $urlParts     = array_filter(explode('/', trim($this->container->Request->environment()->REQUEST_URI, '/')));

        if ($blogPrefix)
        {
            array_shift($urlParts);
        }

        array_shift($urlParts);

        $parent1 = null;
        $parent2 = null;

        if ($requestType === 'category1')
        {
            $this->taxonomySlug = $urlParts[0];
        }
        else if ($requestType === 'category2')
        {
            $parent1 = array_shift($urlParts);
            $this->taxonomySlug = $urlParts[0];
        }
        else if ($requestType === 'category3')
        {
            $parent1 = array_shift($urlParts);
            $parent2 = array_shift($urlParts);
            $this->taxonomySlug = $urlParts[0];
        }

        $this->requestType = 'category';
        $this->queryStr    = 'post_status = published : post_type = post : orderBy = post_created, DESC : category_slug = '.$this->taxonomySlug." : limit = $offset, $perPage";
        $this->posts       = $this->queryParser->parseQuery($this->queryStr);
        $this->postCount   = count($this->posts);

        # Double check if the tag exists
        # and 404 if it does NOT 
        if ($this->postCount === 0)
        {
            if (!$this->SQL->SELECT('id')->FROM('categories')->WHERE('slug', '=', $this->taxonomySlug)->ROW())
            {
                $this->Response->status()->set(404);

                return false;
            }
        }

        # Validate the parent categories exist
        # and are parents of each other
        $category = $this->SQL->SELECT('parent_id')->FROM('categories')->WHERE('slug', '=', $this->taxonomySlug)->ROW();
        
        if ($parent2)
        {
            $parent2 = $this->SQL->SELECT('*')->FROM('categories')->WHERE('slug', '=', $parent2)->ROW();
            
            if (!$parent2 || ($parent2 && $parent2['id'] !== $category['parent_id']))
            {
                $this->Response->status()->set(404);

                return false;
            }

            $parent1 = $this->SQL->SELECT('id')->FROM('categories')->WHERE('slug', '=', $parent1)->ROW();
            
            if (!$parent1 || ($parent1 && $parent1['id'] !== $parent2['parent_id']))
            {
                $this->Response->status()->set(404);

                return false;
            }
        }

        return true;
    }

    /**
     * Filter the posts based on a tag request
     *
     * @access private
     * @return bool 
     */
    private function filterTag(): bool
    {
        $blogPrefix   = $this->Config->get('cms.blog_location');
        $perPage      = $this->Config->get('cms.posts_per_page');
        $offset       = $this->pageIndex * $perPage;
        $urlParts     = array_filter(explode('/', trim($this->container->Request->environment()->REQUEST_URI, '/')));

        $this->requestType  = 'tag';
        $this->taxonomySlug = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $this->queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC : tag_slug = '.$this->taxonomySlug." : limit = $offset, $perPage";
        $this->posts        = $this->queryParser->parseQuery($this->queryStr);
        $this->postCount    = count($this->posts);

        if ($this->postCount === 0)
        {
            if (!$this->SQL->SELECT('id')->FROM('tags')->WHERE('slug', '=', $this->taxonomySlug)->ROW())
            {
                $this->Response->status()->set(404);

                return false;
            }
        }

        return true;
    }

    /**
     * Filter the posts based on an author request
     *
     * @access private
     * @return bool 
     */
    private function filterAuthor(): bool
    {
        $blogPrefix   = $this->Config->get('cms.blog_location');
        $perPage      = $this->Config->get('cms.posts_per_page');
        $offset       = $this->pageIndex * $perPage;
        $urlParts     = array_filter(explode('/', trim($this->container->Request->environment()->REQUEST_URI, '/')));

        $this->requestType  = 'author';
        $this->taxonomySlug = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $this->queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC: author_slug = '.$this->taxonomySlug.": limit = $offset, $perPage";
        $this->posts        = $this->queryParser->parseQuery($this->queryStr);
        $this->postCount    = count($this->posts);

        # Double check if the author exists
        # and that they are an admin or writer
        $role = $this->SQL->SELECT('role')->FROM('users')->WHERE('slug', '=', $this->taxonomySlug)->ROW();

        if ($role)
        {
            if ($role['role'] !== 'administrator' && $role['role'] !== 'writer')
            {
                $this->Response->status()->set(404);

                return false;
            }
        }
        else
        {
            $this->Response->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on search request
     *
     * @access private
     * @return bool 
     */
    private function filterSearch(): bool
    {
        $uri     = trim($this->container->Request->environment()->REQUEST_URI, '/');
        $perPage = $this->Config->get('cms.posts_per_page');
        $offset  = $this->pageIndex * $perPage;

        # Get the query
        $query = $this->Request->fetch('query');
        
        # Validate the query exists
        if (!$query || empty(trim($query)))
        {
            $this->Response->status()->set(404);

            return false;
        }

        # Get the actual search query | sanitize
        $query = htmlspecialchars(trim(strtolower(urldecode(Str::getAfterLastChar($uri, '=')))));
        $query = Str::getBeforeFirstChar($query, '/');

        # No need to query empty strings
        if (empty($query))
        {
            $this->Response->status()->set(404);

            return false;
        }

        # Filter the posts
        $this->queryStr    = "post_status = published : post_type = post : orderBy = post_created, DESC : post_title LIKE $query || post_excerpt LIKE $query : limit = $offset, $perPage";
        $this->posts       = $this->queryParser->parseQuery($this->queryStr);
        $this->postCount   = count($this->posts);
        $this->searchQuery = $query;
        $this->requestType = 'search';

        return true;
    }

    /**
     * Filter the posts based on an attachment request
     *
     * @access private
     * @return bool 
     */
    private function filterAttachment(): bool
    {
        $blogPrefix      = $this->Config->get('cms.blog_location');
        $urlParts        = array_filter(explode('/', trim($this->container->Request->environment()->REQUEST_URI, '/')));
        
        $attachmentName  = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $attachmentSlug  = Str::getBeforeLastChar($attachmentName, '.');
        $attachemmentExt = Str::getAfterLastChar($attachmentName, '.');
        $uploadsUrl      = str_replace($this->Request->environment()->DOCUMENT_ROOT, $this->Request->environment()->HTTP_HOST, $this->Config->get('cms.uploads.path'));
        $isImage         = in_array($attachemmentExt, ['jpg', 'jpeg', 'png', 'gif']);
        $thumbnailSizes  = array_keys($this->Config->get('cms.uploads.thumbnail_sizes'));
        $attachmentURL   = $uploadsUrl.'/'.$attachmentSlug.'.'.$attachemmentExt;
        $attachment      = $this->MediaManager->provider()->byKey('url', $attachmentURL, true);
        $attachmentSize  = 'original';

        # We may need to check if the attachment exists but we are requesting a sized version
        if ($isImage && !$attachment)
        {
            foreach ($thumbnailSizes as $suffix)
            {
                if (Str::contains($attachmentSlug, '_'.$suffix))
                { 
                    $attachmentURL = $uploadsUrl.'/'.Str::getBeforeLastWord($attachmentSlug, '_'.$suffix).'.'.$attachemmentExt;
                    $attachment    = $this->MediaManager->provider()->byKey('url', $attachmentURL, true);
                    if ($attachment)
                    {
                        $attachmentSize = $suffix;
                    }
                }
            }
        }

        # 404 If the attachment does not exist
        if (!$attachment)
        {
            $this->Response->status()->set(404);

            return false;
        }

        $postRow =
        [
            'created'      => $attachment->date,
            'modified'     => $attachment->date,
            'status'       => 'published',
            'type'         => 'attachment',
            'author_id'    => $attachment->uploader_id,
            'title'        => $attachment->title,
            'excerpt'      => $attachment->alt,
            'thumbnail_id' => $attachment->id,
            'comments_enabled' => -1,
        ];

        $this->attachmentSize = $attachmentSize;
        $this->attachmentURL  = $attachmentURL;

        $this->queryStr    = '';
        $this->posts       = [$this->PostManager->provider()->newPost($postRow)];
        $this->postCount   = count($this->posts);
        $this->requestType = 'attachment';

        return true;
    }
}
