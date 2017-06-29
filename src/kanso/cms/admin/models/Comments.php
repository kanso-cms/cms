<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\cms\admin\models\BaseModel;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;

/**
 * Comments model
 *
 * @author Joe J. Howard
 */
class Comments extends BaseModel
{
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
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        $response = [
            'comments'      => $this->loadComments(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        if (!empty($response['comments']))
        {
            $response['max_page'] = $this->loadComments(true);
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

        $commentIds = array_filter(array_map('intval', $this->post['comments']));

        if (!empty($commentIds))
        {
            if ($this->post['bulk_action'] === 'delete')
            {
                $this->delete($commentIds);

                return $this->postMessage('success', 'Your comments were successfully deleted!');
            }
            else if (in_array($this->post['bulk_action'], ['spam', 'pending', 'approved']))
            {
                $this->changeStatus($commentIds, $this->post['bulk_action']);
                
                return $this->postMessage('success', 'Your comments were successfully updated!');
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
        if (!isset($this->post['bulk_action']) || empty($this->post['bulk_action']))
        {
            return false;
        }

        if (!in_array($this->post['bulk_action'], ['spam', 'delete', 'pending', 'approved']))
        {
            return false;
        }

        if (!isset($this->post['comments']) || !is_array($this->post['comments']) || empty($this->post['comments']))
        {
            return false;
        }

        return true;
    }

    /**
     * Delete comments by id
     *
     * @access private
     * @param  array   $ids List of post ids
     * @return bool
     */
    private function delete(array $ids)
    {
        foreach ($ids as $id)
        {
            $comment = $this->CommentManager->byId($id);

            if ($comment)
            {
                $comment->delete();
            }
        }
    }

    /**
     * Change a list of comment statuses
     *
     * @access private
     * @param  array   $ids List of post ids
     * @return bool
     */
    private function changeStatus(array $ids, string $status)
    {
        foreach ($ids as $id)
        {
            $comment = $this->CommentManager->byId($id);

            if ($comment)
            {
                $comment->status = $status;
                $comment->save();
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
            $queries['status'] === false
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

        return $queries;
    }

    /**
     * Returns the list of comments for display
     *
     * @access private
     * @param  bool $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadComments(bool $checkMaxPages = false)
    {
       # Get queries
        $queries = $this->getQueries();

        # Default operation values
        $page         = intval($queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = $queries['sort'] === 'newest' ? 'DESC' : 'ASC' ;
        $sortKey      = 'date';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $search       = $queries['search'];
        $filter       = $queries['status'];
        
        # Filter and sanitize the sort order
        if ($queries['sort'] === 'name')  $sortKey   = 'name';
        if ($queries['sort'] === 'email') $sortKey   = 'email';

        $this->SQL->SELECT('id')->FROM('comments');
        
        # Filter by status
        if ($filter === 'approved')
        {
            $this->SQL->WHERE('status', '=', 'approved');
        }
        if ($filter === 'spam')
        {
            $this->SQL->WHERE('status', '=', 'spam');
        }
        if ($filter === 'pending')
        {
            $this->SQL->WHERE('status', '=', 'pending');
        }
        if ($filter === 'deleted')
        {
            $this->SQL->WHERE('status', '=', 'pending');
        }

        # Is this a search
        if ($search)
        {
            if (Str::contains($search, ':'))
            {
                $keys = explode(':', $search);
                if (in_array($keys[0], ['name', 'email', 'ip_address']))
                {
                    $this->SQL->AND_WHERE($keys[0], 'LIKE', "%$keys[1]%");
                }
            }
            else
            {
                $this->SQL->AND_WHERE('content', 'LIKE', "%$search%");
            }
        }
       
        # Set the order
        $this->SQL->ORDER_BY($sortKey, $sort);

        # Set the limit - Only if we're returning the actual articles
        if (!$checkMaxPages)
        {
            $this->SQL->LIMIT($offset, $limit);
        }

        # Find comments
        $rows = $this->SQL->FIND_ALL();

        # Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($rows) / $perPage);
        }

        # Append custom keys
        $comments = [];
        
        foreach ($rows as $row)
        {
            $comments[] = $this->CommentManager->byId($row['id']);
        }

        return $comments;
    }
}
