<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\cms\wrappers\Category;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;

/**
 * Categories model.
 *
 * @author Joe J. Howard
 */
class Categories extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn())
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
        if ($this->isLoggedIn())
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
            'categories'    => $this->loadCategories(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        if (!empty($response['categories']))
        {
            $response['max_page'] = $this->loadCategories(true);
        }

        return $response;
    }

    /**
     * Parse and validate the POST request from any submitted forms.
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

        $catIds = array_filter(array_map('intval', $this->post['categories']));

        if (!empty($catIds))
        {
            if ($this->post['bulk_action'] === 'delete')
            {
                $this->delete($catIds);

                return $this->postMessage('success', 'Your categories were successfully deleted!');
            }
            if ($this->post['bulk_action'] === 'clear')
            {
                $this->clear($catIds);

                return $this->postMessage('success', 'Your categories were successfully cleared!');
            }
            if ($this->post['bulk_action'] === 'update')
            {
                $update = $this->update(intval($catIds[0]));

                if ($update === 'name_exists')
                {
                    return $this->postMessage('warning', 'Could not update category. Another category with the same name already exists.');
                }

                if ($update === 'slug_exists')
                {
                    return $this->postMessage('warning', 'Could not update category. Another category with the same slug already exists.');
                }

                return $this->postMessage('success', 'Category was successfully updated!');
            }
        }

        return false;
    }

    /**
     * Validates all POST variables are set.
     *
     * @access private
     * @return bool
     */
    private function validatePost(): bool
    {
        // Validation
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }

        if (!isset($this->post['bulk_action']) || empty($this->post['bulk_action']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!in_array($this->post['bulk_action'], ['clear', 'delete', 'update']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!isset($this->post['categories']) || !is_array($this->post['categories']) || empty($this->post['categories']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        return true;
    }

    /**
     * Updates a category.
     *
     * @access private
     * @param  int         $id Single category id
     * @return bool|string
     */
    private function update(int $id)
    {
        if (!isset($this->post['name']) || !isset($this->post['slug']) || !isset($this->post['description']) || !isset($this->post['parent']))
        {
            return false;
        }

        $name        = trim($this->post['name']);
        $slug        = Str::slug($this->post['slug']);
        $description = trim($this->post['description']);
        $parent      = intval($this->post['parent']);
        $category    = $this->CategoryManager->byId($id);

        if (!$category)
        {
            return false;
        }

        // Validate category with same name does not already exist
        $existsName = $this->CategoryManager->byName($name);

        if ($existsName && $existsName->id !== $id)
        {
            return 'name_exists';
        }

        // Validate category with same slug does not already exist
        $existsSlug = $this->CategoryManager->bySlug($slug);

        if ($existsSlug && $existsSlug->id !== $id)
        {
            return 'slug_exists';
        }

        $category->name = $name;
        $category->slug = $slug;
        $category->parent_id = $parent;
        $category->description = $description;
        $category->save();
        $this->resetPostSlugs();

        return true;
    }

    /**
     * Delete articles by id.
     *
     * @access private
     * @param array $ids List of post ids
     */
    private function delete(array $ids)
    {
        foreach ($ids as $id)
        {
            $category = $this->CategoryManager->byId($id);

            if ($category)
            {
                $category->delete();

                $this->resetPostSlugs();
            }
        }
    }

    /**
     * Clear tags of articles.
     *
     * @access private
     * @param array $ids List of post ids
     */
    private function clear(array $ids)
    {
        foreach ($ids as $id)
        {
            $category = $this->CategoryManager->byId($id);

            if ($category)
            {
                $category->clear();

                $this->resetPostSlugs();
            }
        }
    }

    /**
     * Check if the GET URL queries are either empty or set to defaults.
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
     * Returns the requested GET queries with defaults.
     *
     * @access private
     * @return array
     */
    private function getQueries(): array
    {
        // Get queries
        $queries = $this->Request->queries();

        // Set defaults
        if (!isset($queries['search'])) $queries['search'] = false;
        if (!isset($queries['page']))   $queries['page']   = 0;
        if (!isset($queries['sort']))   $queries['sort']   = 'name';

        return $queries;
    }

    /**
     * Returns the list of categories for display.
     *
     * @access private
     * @param  bool      $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadCategories(bool $checkMaxPages = false)
    {
       // Get queries
        $queries = $this->getQueries();

        // Default operation values
        $page         = ((int) $queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'name';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $search       = $queries['search'];

        // Select the posts
        $this->sql()->SELECT('categories.id')->FROM('categories');

        // Search the name
        if ($search)
        {
            $this->sql()->AND_WHERE('name', 'like', '%' . $queries['search'] . '%');
        }

        // Find the articles
        $rows = $this->sql()->FIND_ALL();

        // Add all the article count
        $result = [];

        foreach ($rows as $row)
        {
            $this->sql()->SELECT('posts.id')->FROM('posts')
            ->LEFT_JOIN_ON('categories_to_posts', 'posts.id = categories_to_posts.post_id')
            ->LEFT_JOIN_ON('categories', 'categories.id = categories_to_posts.category_id')
            ->WHERE('categories.id', '=', $row['id']);

            $category = $this->CategoryManager->byId($row['id']);

            $category->article_count = count($this->sql()->FIND_ALL());

            $result[] = $category;
        }

        // If we're sorting by article count, we need to paginate
        // all the results and return the requested page of categories
        if (!$checkMaxPages)
        {
            if ($queries['sort'] === 'name')
            {
                if (!$search)
                {
                    foreach ($result as $i => $category)
                    {
                        if ($category->parent_id > 0)
                        {
                            unset($result[$i]);
                        }
                    }

                    $result = Arr::sortMulti($result, 'name');
                    $withChildren = [];

                    foreach ($result as $i => $category)
                    {
                        $withChildren[] = $category;
                        $children       = $this->recursiveChildren($category);

                        if ($children)
                        {
                            $withChildren = array_merge($withChildren, $children);
                        }
                    }

                    $result = $withChildren;
                }
            }
            else
            {
                $result = Arr::sortMulti($result, 'article_count');
            }

        }

        // Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($result) / $perPage);
        }

        $result = Arr::paginate($result, $page, $perPage);

        if (!$result)
        {
            return [];
        }

        return $result[$page];
    }

    /**
     * Recursively get category children.
     *
     * @access private
     * @param  \kanso\cms\wrappers\Category $parent   Category parent object
     * @param  array                        $children Category parent children (optional) (default [])
     * @return array
     */
    private function recursiveChildren(Category $parent, $children = []): array
    {
        foreach ($parent->children() as $child)
        {
            $children[] = $child;
            $children = array_merge($children, $this->recursiveChildren($child));
        }

        return $children;
    }

    /**
     * Update and reset post slugs when permalinks have changed.
     *
     * @access private
     */
    private function resetPostSlugs()
    {
        // Select the posts
        $posts = $this->sql()->SELECT('posts.id')->FROM('posts')->FIND_ALL();

        foreach ($posts as $row)
        {
            $post = $this->PostManager->byId($row['id']);

            $post->save();
        }
    }
}
