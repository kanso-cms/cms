<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use Exception;
use kanso\cms\admin\models\BaseModel;
use kanso\framework\utility\Str;
use kanso\framework\utility\Humanizer;
/**
 * Posts
 *
 * @author Joe J. Howard
 */
class Posts extends BaseModel
{
    /**
     * the post type to filter
     *
     * @var string
     */
    protected $postType;

    /**
     * Set the post type to filter
     *
     * @access public
     * @param  array   $ids List of post ids
     */
    public function setPostType(string $postType)
    {
        $this->postType = $postType;
    }

    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn)
        {
            return $this->parseGet();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        if ($this->isLoggedIn)
        {
            return $this->parsePost();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        return false;
    }

    /**
     * Parse the $_GET request variables and filter the posts for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        # Prep the response
        $response =
        [
            'posts'         => $this->loadPosts(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
            'postType'      => $this->postType,
            'postSlug'      => Str::getAfterLastChar(trim(Str::getBeforeFirstChar($this->Request->environment()->REQUEST_URI, '?'), '/'), '/'),
            'postName'      => Humanizer::pluralize(ucfirst(Str::camel2case($this->postType))),
        ];

        # If the posts are empty,
        # There's no need to check for max pages
        if (!empty($response['posts']))
        {
            $response['max_page'] = $this->loadPosts(true);
        }

        return $response;
    }

    /**
     * Parse and validate the POST request from any submitted forms
     * 
     * @access private
     * @return array|false
     */
    public function parsePost()
    {
        if (!$this->validatePost())
        {
            return false;
        }

        $postIds = array_filter(array_map('intval', $this->post['posts']));

        if (!empty($postIds))
        {
            if ($this->post['bulk_action'] === 'delete')
            {
                $this->delete($postIds);

                return $this->postMessage('success', 'Your posts were successfully deleted!');
            }
            if ($this->post['bulk_action'] === 'published' || $this->post['bulk_action'] === 'draft')
            {
                $this->changeStatus($postIds, $this->post['bulk_action']);
                
                return $this->postMessage('success', 'Your posts were successfully updated!');
            }
        }

        return false;
    }

    /**
     * Validates all POST variables are set
     * 
     * @access private
     * @return bool
     */
    private function validatePost(): bool
    {
        # Validation
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new Exception('Bad Admin Panel POST Request. The CSRF token was either not provided or invalid.');
        }
        
        if (!isset($this->post['bulk_action']) || empty($this->post['bulk_action']))
        {
            return false;
        }

        if (!in_array($this->post['bulk_action'], ['published', 'draft', 'delete']))
        {
            return false;
        }

        if (!isset($this->post['posts']) || !is_array($this->post['posts']) || empty($this->post['posts']))
        {
            return false;
        }

        return true;
    }

    /**
     * Delete articles by id
     *
     * @access private
     * @param  array   $ids List of post ids
     */
    private function delete(array $ids)
    {
        foreach ($ids as $id)
        {
            $post = $this->PostManager->byId($id);

            if ($post)
            {
                $post->delete();
            }
        }
    }

    /**
     * Change articles status
     *
     * @access private
     * @param  array   $ids    List of post ids
     * @param  string  $status Post status to change to
     */
    private function changeStatus(array $ids, string $status)
    {
        foreach ($ids as $id)
        {
            $post = $this->PostManager->byId($id);

            if ($post)
            {
                $post->status = $status;

                $post->save();
            }
        }
    }

    /**
     * Check if the GET URL queries are either empty or set to defaults
     *
     * @access private
     * @return bool
     */
    private function emptyQueries(): bool
    {
        $queries = $this->getQueries();

        return (
            $queries['search'] === false && 
            $queries['page']   === 0 && 
            $queries['sort']   === 'newest' && 
            $queries['status'] === false && 
            $queries['author'] === false && 
            $queries['tag'] === false && 
            $queries['category'] === false
        );
    }

    /**
     * Returns the requested GET queries with defaults
     *
     * @access private
     * @return array
     */
    private function getQueries(): array
    {
        # Get queries
        $queries = $this->Request->queries();

        # Set defaults
        if (!isset($queries['search']))   $queries['search']   = false;
        if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'newest';
        if (!isset($queries['status']))   $queries['status']   = false;
        if (!isset($queries['author']))   $queries['author']   = false;
        if (!isset($queries['tag']))      $queries['tag']      = false;
        if (!isset($queries['category'])) $queries['category'] = false;

        return $queries;
    }

    /**
     * Returns the list of articles for display
     *
     * @access private
     * @param  bool $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadPosts(bool $checkMaxPages = false)
    {
        # Get queries
        $queries = $this->getQueries();

        # Default operation values
        $page         = ((int)$queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'posts.created';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $status       = $queries['status'];
        $search       = $queries['search'];
        $author       = $queries['author'];
        $tag          = $queries['tag'];
        $category     = $queries['category'];

        # Filter and sanitize the sort order
        if ($queries['sort'] === 'newest' || $queries['sort'] === 'published') $sort = 'DESC';
        if ($queries['sort'] === 'oldest' || $queries['sort'] === 'drafts') $sort = 'ASC';

        if ($queries['sort'] === 'category')  $sortKey   = 'categories.name';
        if ($queries['sort'] === 'tags')      $sortKey   = 'tags.name';
        if ($queries['sort'] === 'drafts')    $sortKey   = 'posts.status';
        if ($queries['sort'] === 'published') $sortKey   = 'posts.status';
        if ($queries['sort'] === 'type')      $sortKey   = 'posts.type';
        if ($queries['sort'] === 'title')     $sortKey   = 'posts.title';

        # Select the posts
        $this->SQL->SELECT('posts.id')->FROM('posts')->WHERE('posts.type', '=', $this->postType);
        
        # Set the order
        $this->SQL->ORDER_BY($sortKey, $sort);

        # Apply basic joins for queries
        $this->SQL->LEFT_JOIN_ON('users', 'users.id = posts.author_id');
        $this->SQL->LEFT_JOIN_ON('comments', 'comments.post_id = posts.id');
        $this->SQL->LEFT_JOIN_ON('categories', 'posts.category_id = categories.id');
        $this->SQL->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id');
        $this->SQL->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id');
        $this->SQL->GROUP_BY('posts.id');

        # Filter status/published
        if ($status === 'published')
        {
            $this->SQL->AND_WHERE('posts.status', '=', 'published');
        }
        else if ($status === 'drafts')
        {
            $this->SQL->AND_WHERE('posts.status', '=', 'draft');
        }

        # Search the title
        if ($search)
        {
            $this->SQL->AND_WHERE('posts.title', 'like', '%'.$queries['search'].'%');
        }

        # Filter by author
        if ($author)
        {
            $this->SQL->AND_WHERE('posts.author_id', '=', intval($author));
        }

        # Filter by tag
        if ($tag)
        {
            $this->SQL->AND_WHERE('tags.id', '=', intval($tag));
        }

        # Filter by category
        if ($category)
        {
            $this->SQL->AND_WHERE('category_id', '=', intval($category));
        }

        # Set the limit - Only if we're returning the actual articles
        if (!$checkMaxPages)
        {
            $this->SQL->LIMIT($offset, $limit);
        }

        # Find the articles
        $rows = $this->SQL->FIND_ALL();

        # Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($rows) / $perPage);
        }

        $articles = [];
        
        foreach ($rows as $row)
        {
           $articles[] = $this->PostManager->byId($row['id']);
        }

        return $articles;
    }
}
