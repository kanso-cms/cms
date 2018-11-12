<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\utility\Str;

/**
 * CMS Query filter methods.
 *
 * @author Joe J. Howard
 */
class Filter extends Helper
{
    /**
     * Reset the internal properties to default.
     *
     * @access public
     */
    public function reset()
    {
        $this->parent->pageIndex    = 0;
        $this->parent->postIndex    = -1;
        $this->parent->postCount    = 0;
        $this->parent->posts        = [];
        $this->parent->requestType  = null;
        $this->parent->queryStr     = null;
        $this->parent->post         = null;
        $this->parent->taxonomySlug = null;
        $this->parent->searchQuery  = null;
    }

    /**
     * Fetch and set the currently requested page.
     *
     * @access public
     */
    public function fetchPageIndex()
    {
        $this->parent->pageIndex = $this->container->get('Request')->fetch('page');

        $this->parent->pageIndex = $this->parent->pageIndex === 1 || $this->parent->pageIndex === 0 ? 0 : $this->parent->pageIndex-1;
    }

    /**
     * Apply a query for a custom string.
     *
     * @access public
     * @param string $queryStr Query string to parse
     */
    private function applyQuery(string $queryStr)
    {
        $this->reset();

        $this->parent->queryStr = trim($queryStr);

        $this->parent->posts = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);

        $this->parent->postCount = count($this->parent->posts);

        $this->parent->requestType = 'custom';

        if (isset($this->parent->posts[0]))
        {
            $this->parent->post = $this->parent->posts[0];
        }
    }

    /**
     * Filter the posts by the request type.
     *
     * Note this method is used from the router/CMS core to filter posts based
     * on the matched route.
     *
     * @access public
     * @param string $requestType The requested page type
     */
    public function filterPosts(string $requestType)
    {
        // Reset the internal properties
        $this->reset();

        // Reset the response to 200
        $this->container->get('Response')->status()->set(200);

        // Reset the page index
        $this->fetchPageIndex();

        // Filter and paginate the posts based on the request type
        if ($requestType === 'home' || $requestType === 'home-page')
        {
            if (!$this->filterHome($requestType))
            {
                return false;
            }
        }
        elseif ($requestType === 'tag')
        {
            if (!$this->filterTag())
            {
                return false;
            }
        }
        elseif ($requestType === 'category')
        {
            if (!$this->filterCategory())
            {
                return false;
            }
        }
        elseif ($requestType === 'author')
        {
            if (!$this->filterAuthor())
            {
                return false;
            }
        }
        elseif ($requestType === 'single' || Str::getBeforeFirstChar($requestType, '-') === 'single')
        {
            if (!$this->filterSingle($requestType))
            {
                return false;
            }
        }
        elseif ($requestType === 'page')
        {
            if (!$this->filterPage())
            {
                return false;
            }
        }
        elseif ($requestType === 'search')
        {
            if (!$this->filterSearch())
            {
                return false;
            }
        }
        elseif ($requestType === 'attachment')
        {
            if (!$this->filterAttachment())
            {
                return false;
            }
        }
        elseif ($requestType === 'sitemap')
        {
            if (!$this->filterSitemap())
            {
                return false;
            }
        }

        // Set the_post so we're looking at the first item
        if (isset($this->parent->posts[0]))
        {
            $this->parent->post = $this->parent->posts[0];
        }
    }

    /**
     * Filter the posts based on a category request.
     *
     * @access private
     * @param  string $requestType The incoming request type ('home'|'home-blog')
     * @return bool
     */
    private function filterHome(string $requestType): bool
    {
        $perPage   = $this->container->get('Config')->get('cms.posts_per_page');
        $offset    = $this->parent->pageIndex * $perPage;

        $this->parent->requestType = $requestType;
        $this->parent->queryStr    = "post_status = published : post_type = post : orderBy = post_created, DESC : limit = $offset, $perPage";
        $this->parent->posts       = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
        $this->parent->postCount   = count($this->parent->posts);

        if ($this->parent->postCount === 0)
        {
            $this->container->get('Response')->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on a single request.
     *
     * @access private
     * @param  string $requestType The incoming request type ('single'|'$custom-single')
     * @return bool
     */
    private function filterSingle(string $requestType): bool
    {
        $blogPrefix = $this->container->get('Config')->get('cms.blog_location');
        $uri        = trim($this->container->Request->environment()->REQUEST_URI, '/');
        $postType   = $requestType === 'single' ? 'post' : Str::getAfterFirstChar($requestType, '-');

        $this->parent->requestType = $requestType;

        if ($this->container->get('Request')->fetch('query') === 'draft' && $this->container->get('Gatekeeper')->isAdmin())
        {
            $uri       = Str::queryFilterUri($uri);
            $uri       = !empty($blogPrefix) ? str_replace($blogPrefix . '/', '', $uri) : $uri;
            $this->parent->queryStr  = 'post_status = draft : post_type = ' . $postType . ' : post_slug = ' . $uri . '/';
            $this->parent->posts     = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
            $this->parent->postCount = count($this->parent->posts);
        }
        else
        {
            $uri = Str::getBeforeLastWord(Str::queryFilterUri($uri), '/feed');
            $uri = !empty($blogPrefix) ? str_replace($blogPrefix . '/', '', $uri) : $uri;

            $this->parent->queryStr  = 'post_status = published : post_type = ' . $postType . ' : post_slug = ' . $uri . '/';
            $this->parent->posts     = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
            $this->parent->postCount = count($this->parent->posts);
        }

        if ($this->parent->postCount === 0)
        {
            $this->container->get('Response')->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on a page request.
     *
     * @access private
     * @return bool
     */
    private function filterPage(): bool
    {
        $uri = trim($this->container->Request->environment()->REQUEST_URI, '/');
        $this->parent->requestType = 'page';

        if ($this->container->get('Request')->fetch('query') === 'draft' && $this->container->get('Gatekeeper')->isAdmin())
        {
            $uri             = Str::queryFilterUri($uri);
            $this->parent->queryStr  = 'post_status = draft : post_type = page : post_slug = ' . $uri . '/';
            $this->parent->posts     = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
            $this->parent->postCount = count($this->parent->posts);
        }
        else
        {
            $uri = Str::getBeforeLastWord(Str::queryFilterUri($uri), '/feed');
            $this->parent->queryStr   = 'post_status = published : post_type = page : post_slug = ' . $uri . '/';
            $this->parent->posts      = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
            $this->parent->postCount  = count($this->parent->posts);
        }

        if ($this->parent->postCount === 0)
        {
            $this->container->get('Response')->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on an category request.
     *
     * @access private
     * @return bool
     */
    private function filterCategory(): bool
    {
        $blogPrefix   = $this->container->get('Config')->get('cms.blog_location');
        $perPage      = $this->container->get('Config')->get('cms.posts_per_page');
        $offset       = $this->parent->pageIndex * $perPage;
        $urlParts     = explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI));
        $isPage       = in_array('page', $urlParts);
        $isFeed       = in_array('feed', $urlParts);

        if ($blogPrefix)
        {
            array_shift($urlParts);
        }

        array_shift($urlParts);

        // Remove /page/number/
        if ($isPage)
        {
            array_pop($urlParts);
            array_pop($urlParts);
        }

        // Remove /feed/rss
        if ($isFeed)
        {
            $last = array_values(array_slice($urlParts, -1))[0];

            if ($last === 'rss' || $last === 'rdf' || $last == 'atom')
            {
                array_pop($urlParts);
                array_pop($urlParts);
            }
            else
            {
                array_pop($urlParts);
            }
        }

        // Get the last category slug
        $lastCat = $this->container->get('CategoryManager')->provider()->byKey('slug', array_values(array_slice($urlParts, -1))[0], true);

        // Make sure the category exists
        if (!$lastCat)
        {
            $this->container->get('Response')->status()->set(404);

            return false;
        }

        // Make sure the path to a nested category is correct
        if (!$this->parent->the_category_slug($lastCat->id) === implode('/', $urlParts))
        {
            $this->container->get('Response')->status()->set(404);

            return false;
        }

        $this->parent->requestType  = 'category';
        $this->parent->taxonomySlug = array_pop($urlParts);
        $this->parent->queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC : category_slug = ' . $this->parent->taxonomySlug . " : limit = $offset, $perPage";
        $this->parent->posts        = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
        $this->parent->postCount    = count($this->parent->posts);

        // If there are no posts and the page is more than 2 return false
        if ($this->parent->postCount === 0 && $this->parent->pageIndex >= 1)
        {
            $this->reset();

            $this->container->get('Response')->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on a tag request.
     *
     * @access private
     * @return bool
     */
    private function filterTag(): bool
    {
        $blogPrefix   = $this->container->get('Config')->get('cms.blog_location');
        $perPage      = $this->container->get('Config')->get('cms.posts_per_page');
        $offset       = $this->parent->pageIndex * $perPage;
        $urlParts     = array_filter(explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI)));

        $this->parent->requestType  = 'tag';
        $this->parent->taxonomySlug = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $this->parent->queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC : tag_slug = ' . $this->parent->taxonomySlug . " : limit = $offset, $perPage";
        $this->parent->posts        = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
        $this->parent->postCount    = count($this->parent->posts);

        // If there are no posts and the page is more than 2 return false
        if ($this->parent->postCount === 0 && $this->parent->pageIndex >= 1)
        {
            $this->reset();

            $this->container->get('Response')->status()->set(404);

            return false;
        }

        if ($this->parent->postCount === 0)
        {
            if (!$this->sql()->SELECT('id')->FROM('tags')->WHERE('slug', '=', $this->parent->taxonomySlug)->ROW())
            {
                $this->container->get('Response')->status()->set(404);

                return false;
            }
        }

        return true;
    }

    /**
     * Filter the posts based on an author request.
     *
     * @access private
     * @return bool
     */
    private function filterAuthor(): bool
    {
        $blogPrefix   = $this->container->get('Config')->get('cms.blog_location');
        $perPage      = $this->container->get('Config')->get('cms.posts_per_page');
        $offset       = $this->parent->pageIndex * $perPage;
        $urlParts     = array_filter(explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI)));

        $this->parent->requestType  = 'author';
        $this->parent->taxonomySlug = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $this->parent->queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC: author_slug = ' . $this->parent->taxonomySlug . ": limit = $offset, $perPage";
        $this->parent->posts        = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
        $this->parent->postCount    = count($this->parent->posts);

        // Double check if the author exists
        // and that they are an admin or writer
        $role = $this->sql()->SELECT('role')->FROM('users')->WHERE('slug', '=', $this->parent->taxonomySlug)->ROW();

        if ($role)
        {
            if ($role['role'] !== 'administrator' && $role['role'] !== 'writer')
            {
                $this->container->get('Response')->status()->set(404);

                return false;
            }
        }
        else
        {
            $this->container->get('Response')->status()->set(404);

            return false;
        }

        return true;
    }

    /**
     * Filter the posts based on search request.
     *
     * @access private
     * @return bool
     */
    private function filterSearch(): bool
    {
        $uri     = trim($this->container->Request->environment()->REQUEST_URI, '/');
        $perPage = $this->container->get('Config')->get('cms.posts_per_page');
        $offset  = $this->parent->pageIndex * $perPage;

        // Get the query
        $query = $this->container->get('Request')->queries('q');

        if (!$query || empty(trim($query)))
        {
            $query = '';
        }

        // Get the actual search query | sanitize
        $query = htmlspecialchars(trim(strtolower(urldecode(Str::getAfterLastChar($uri, '=')))));
        $query = Str::getBeforeFirstChar($query, '/');

        // Validate the query exists
        if (!$query || empty(trim($query)))
        {
            // Empty search results
            $this->parent->queryStr    = '';
            $this->parent->posts       = [];
            $this->parent->postCount   = count($this->parent->posts);
            $this->parent->searchQuery = '';
            $this->parent->requestType = 'search';

            return true;
        }

        // Filter the posts
        $this->parent->queryStr    = "post_status = published : post_type != page : orderBy = post_created, DESC : post_title LIKE $query : limit = $offset, $perPage";
        $this->parent->posts       = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
        $this->parent->postCount   = count($this->parent->posts);
        $this->parent->searchQuery = $query;
        $this->parent->requestType = 'search';

        return true;
    }

    /**
     * Filter the posts based on an attachment request.
     *
     * @access private
     * @return bool
     */
    private function filterAttachment(): bool
    {
        $blogPrefix      = $this->container->get('Config')->get('cms.blog_location');
        $urlParts        = array_filter(explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI)));

        $attachmentName  = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $attachmentSlug  = Str::getBeforeLastChar($attachmentName, '.');
        $attachemmentExt = Str::getAfterLastChar($attachmentName, '.');
        $uploadsUrl      = str_replace($this->container->get('Request')->environment()->DOCUMENT_ROOT, $this->container->get('Request')->environment()->HTTP_HOST, $this->container->get('Config')->get('cms.uploads.path'));
        $isImage         = in_array($attachemmentExt, ['jpg', 'jpeg', 'png', 'gif']);
        $thumbnailSizes  = array_keys($this->container->get('Config')->get('cms.uploads.thumbnail_sizes'));
        $attachmentURL   = $uploadsUrl . '/' . $attachmentSlug . '.' . $attachemmentExt;
        $attachment      = $this->container->get('MediaManager')->provider()->byKey('url', $attachmentURL, true);
        $attachmentSize  = 'original';

        // We may need to check if the attachment exists but we are requesting a sized version
        if ($isImage && !$attachment)
        {
            foreach ($thumbnailSizes as $suffix)
            {
                if (Str::contains($attachmentSlug, '_' . $suffix))
                {
                    $attachmentURL = $uploadsUrl . '/' . Str::getBeforeLastWord($attachmentSlug, '_' . $suffix) . '.' . $attachemmentExt;
                    $attachment    = $this->container->get('MediaManager')->provider()->byKey('url', $attachmentURL, true);
                    if ($attachment)
                    {
                        $attachmentSize = $suffix;
                    }
                }
            }
        }

        // 404 If the attachment does not exist
        if (!$attachment)
        {
            $this->container->get('Response')->status()->set(404);

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

        $this->parent->attachmentSize = $attachmentSize;
        $this->parent->attachmentURL  = $attachmentURL;

        $this->parent->queryStr    = '';
        $this->parent->posts       = [$this->container->get('PostManager')->provider()->newPost($postRow)];
        $this->parent->postCount   = count($this->parent->posts);
        $this->parent->requestType = 'attachment';

        return true;
    }

    /**
     * Filter the posts for sitemap.
     *
     * @access private
     * @param  string $requestType The incoming request type ('home'|'home-blog')
     * @return bool
     */
    private function filterSitemap(): bool
    {
        $this->parent->requestType = 'sitemap';
        $this->parent->queryStr    = 'post_status = published : post_type = post : orderBy = post_created';
        $this->parent->posts       = $this->parent->helpers['parser']->parseQuery($this->parent->queryStr);
        $this->parent->postCount   = count($this->parent->posts);

        return true;
    }

    /**
     * Filter the posts based on an attachment request.
     *
     * @access private
     * @param  int   $parentId Parent category id
     * @param  array $slugs    Array of category slugs
     * @return array
     */
    private function childrenCategories(int $parentId, array $slugs = [])
    {
        $children = $this->sql()->SELECT('*')->FROM('categories')->WHERE('parent_id', '=', $parentId)->FIND_ALL();

        foreach ($children as $child)
        {
            $slugs[] = $child['slug'];
            $slugs   = array_unique(array_merge($slugs, $this->childrenCategories($child['id'], $slugs)));
        }

        return $slugs;
    }
}
