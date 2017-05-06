<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Admin\Models;

use Kanso\Kanso;
use Kanso\Framework\Utility\Arr;
use Kanso\CMS\Admin\Models\Model;
use Kanso\CMS\Wrappers\Managers\TagManager;

/**
 * Tags page model
 *
 * @author Joe J. Howard
 */
class Tags extends Model
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
     * Returns the tag manager
     *
     * @access private
     * @return \Kanso\CMS\Wrappers\Managers\TagManager
     */
    private function tagManager(): TagManager
    {
        return Kanso::instance()->TagManager;
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
            'tags'         => $this->loadTags(),
            'max_page'     => 0,
            'queries'      => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        if (!empty($response['tags']))
        {
            $response['max_page'] = $this->loadTags(true);
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

        $tagIds = array_filter(array_map('intval', $this->post['tags']));

        if (!empty($tagIds))
        {
            if ($this->post['bulk_action'] === 'delete')
            {
                $this->delete($tagIds);

                return $this->postMessage('success', 'Your tags were successfully deleted!');
            }
            if ($this->post['bulk_action'] === 'clear')
            {
                $this->clear($tagIds);
                
                return $this->postMessage('success', 'Your tags were successfully cleared!');
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

        if (!in_array($this->post['bulk_action'], ['clear', 'delete']))
        {
            return false;
        }

        if (!isset($this->post['tags']) || !is_array($this->post['tags']) || empty($this->post['tags']))
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
     * @return bool
     */
    private function delete(array $ids)
    {
        foreach ($ids as $id)
        {
            $tag = $this->tagManager()->byId($id);

            if ($tag)
            {
                $tag->delete();
            }
        }
    }

    /**
     * Clear tags of articles
     *
     * @access private
     * @param  array   $ids List of post ids
     * @return bool
     */
    private function clear(array $ids)
    {
        foreach ($ids as $id)
        {
            $tag = $this->tagManager()->byId($id);

            if ($tag)
            {
                $tag->clear();
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
            $queries['sort']   === 'name'
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
        $queries = $this->request->queries();

        # Set defaults
        if (!isset($queries['search'])) $queries['search'] = false;
        if (!isset($queries['page']))   $queries['page']   = 0;
        if (!isset($queries['sort']))   $queries['sort']   = 'name';

        return $queries;
    }

    /**
     * Returns the list of tags for display
     *
     * @access private
     * @param  bool $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadTags(bool $checkMaxPages = false)
    {
       # Get queries
        $queries = $this->getQueries();

        # Default operation values
        $page         = ((int)$queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'name';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $search       = $queries['search'];

        # Select the posts
        $this->SQL->SELECT('tags.id')->FROM('tags');

        # Set the limit - Only if we're returning the actual tag list
        # and not sorting by article count
        if (!$checkMaxPages && $queries['sort'] === 'name')
        {
            $this->SQL->LIMIT($offset, $limit);
            
            $this->SQL->ORDER_BY($sortKey, $sort);
        }
        
        # Search the name
        if ($search)
        {
            $this->SQL->AND_WHERE('name', 'like', '%'.$queries['search'].'%');
        }

        # Find the articles
        $rows = $this->SQL->FIND_ALL();

        # Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($rows) / $perPage);
        }

        # Add all the article count
        $result = [];
        foreach ($rows as $row)
        {
            $this->SQL->SELECT('posts.id')->FROM('posts')
            ->LEFT_JOIN_ON('tags_to_posts', 'posts.id = tags_to_posts.post_id')
            ->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id')
            ->WHERE('tags.id', '=', $row['id']);

            $tag = $this->tagManager()->byId($row['id']);
            
            $tag->article_count = count($this->SQL->FIND_ALL());

            $result[] = $tag;
        }

        # If we're sorting by article count, we need to paginate
        # all the results and return the requested page of tags
        if ($queries['sort'] !== 'name' && !$checkMaxPages)
        {
            $result = Arr::sortMulti($result, 'article_count');
            
            $result = Arr::paginate($result, $page, $perPage);

            if (isset($result[0]))
            {
                return $result[0];
            }
            
            return [];
        }

        return $result;
    }
}
