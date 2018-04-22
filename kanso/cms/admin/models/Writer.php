<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\cms\admin\models\BaseModel;
use kanso\framework\utility\Str;
use kanso\framework\utility\Markdown;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;

/**
 * Comments model
 *
 * @author Joe J. Howard
 */
class Writer extends BaseModel
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
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }
        
        if (isset($this->post['ajax_request']))
        {
            $request = $this->post['ajax_request'];

            if ($request === 'writer_publish_article')
            {
                return $this->publishArticle();
            } 
            else if ($request === 'writer_save_existing_article')
            {
                return $this->saveExistingArticle();
            }
            else if ($request === 'writer_save_new_article')
            {
                return $this->saveNewArticle();
            }
        }

        throw new RequestException('Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
    }

    /**
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        $queries   = $this->Request->queries();
        $post      = false;
        
        if (isset($queries['id']))
        {
            $post = $this->PostManager->byId(intval($queries['id']));
        }

        return [ 'the_post' => $post ];
    }

    /**
     * Publish an existing or new article
     *
     * @access private
     * @return array
     */
    private function publishArticle()
    {
        $this->post['status'] = 'published';

        if (isset($this->post['id']) && !empty($this->post['id']) && is_numeric($this->post['id']))
        {
            return $this->saveExistingArticle();
        }
        else
        {
            return $this->saveNewArticle();
        }
    }

    /**
     * Save an existing article
     *
     * @access private
     * @return array|false
     */
    private function saveExistingArticle() 
    {
        # Sanitize and validate the POST variables
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'type' => 'required',
            'id'   => 'required|integer',
        ]);

        $this->validation->filter_rules([
            'title'        => 'trim|sanitize_string',
            'category'     => 'trim|sanitize_string',
            'tags'         => 'trim|sanitize_string',
            'type'         => 'trim|sanitize_string',
            'excerpt'      => 'trim|sanitize_string',
            'status'       => 'trim|sanitize_string',
            'id'           => 'trim|sanitize_numbers',
            'thumbnail_id' => 'trim|sanitize_numbers',
            'author'       => 'trim|sanitize_numbers',
        ]);

        $validated_data = $this->validation->run($post);
        
        if (!$validated_data)
        {
            return false;
        }

        $validated_data['id'] = intval($validated_data['id']);
        $validated_data['author'] = intval($validated_data['author']);
        
        $article = $this->PostManager->byId($validated_data['id']);

        $postMeta = $this->getPostMeta();
        
        if (!$article)
        {
            return false;
        }

        $article->title            = $validated_data['title'];
        $article->categories       = $validated_data['category'];
        $article->tags             = $validated_data['tags'];
        $article->excerpt          = $validated_data['excerpt'];
        $article->type             = $validated_data['type'];
        $article->author_id        = $validated_data['author'];
        $article->comments_enabled = Str::bool($validated_data['comments']);
        $article->meta             = !empty($postMeta) ? $postMeta : null;

        if (isset($_POST['content']))
        {
            $article->content = $_POST['content'];
        }

        if (isset($validated_data['status']))
        {
            $article->status = $validated_data['status'];
        }

        if (empty($validated_data['excerpt']))
        {
            $article->excerpt = $_POST['content'];
        }
        else
        {
            if (empty($article->excerpt))
            {
                $article->excerpt = $validated_data['excerpt'];
            }
            else
            {
                $article->excerpt = $article->excerpt;
            }
        }

        if (!empty($validated_data['thumbnail_id']))
        {
            $article->thumbnail_id = intval($validated_data['thumbnail_id']);
        }
        else
        {
            $article->thumbnail_id = null;
        }

        if ($article->save())
        {
            # Clear from cache
            if ($this->Config->get('cache.http_cache_enabled') === true)
            {
                $key = $this->Config->get('cms.blog_location').'/'.$this->Query->the_slug($article->id);
                $key = Str::alphaDash($key);
                $this->Cache->delete($key);
            }

            # Return slug/id
            $suffix = $article->status === 'published' ? '' : '?draft';

            if ($validated_data['type'] === 'post')
            {
                $blogPrefix = $this->Config->get('cms.blog_location');

                return ['id' => $article->id, 'slug' => !$blogPrefix ? $article->slug.$suffix : $blogPrefix.'/'.$article->slug.$suffix];
            }

            return ['id' => $article->id, 'slug' => $article->slug.$suffix];
        }

        return false;
    }

    /**
     * Save a new article
     *
     * @access private
     * @return array|false
     */
    private function saveNewArticle() 
    {
        # Sanitize and validate the POST variables
        $post = $this->validation->sanitize($this->post);

        $this->validation->validation_rules([
            'type' => 'required',
        ]);

        $this->validation->filter_rules([
            'title'        => 'trim|sanitize_string',
            'category'     => 'trim|sanitize_string',
            'tags'         => 'trim|sanitize_string',
            'type'         => 'trim|sanitize_string',
            'excerpt'      => 'trim|sanitize_string',
            'status'       => 'trim|sanitize_string',
            'thumbnail_id' => 'trim|sanitize_numbers',
            'author'       => 'trim|sanitize_numbers',
        ]);

        $validated_data = $this->validation->run($post);

        if (!$validated_data)
        {
            return false;
        }

        # Get the article content directly from the _POST global
        # so it is not filtered in any way
        if (isset($_POST['content']))
        {
            $validated_data['content'] = $_POST['content'];
        }

        # Default is to save as draft
        if (!isset($validated_data['status']))
        {
            $validated_data['status'] = 'draft';
        }

        $validated_data['author'] = intval($validated_data['author']);

        $postMeta = $this->getPostMeta();

        $excerpt = '';

        if (empty($validated_data['excerpt']))
        {
            $excerpt = $validated_data['content'];
        }
        else
        {
            $excerpt = $validated_data['excerpt'];
        }

        $post = $this->PostManager->create([
            'title'        => $validated_data['title'],
            'categories'   => $validated_data['category'],
            'tags'         => $validated_data['tags'],
            'excerpt'      => $excerpt,
            'thumbnail_id' => $validated_data['thumbnail_id'],
            'status'       => $validated_data['status'],
            'type'         => $validated_data['type'],
            'author_id'    => $validated_data['author'],
            'content'      => !empty($_POST['content']) ? $_POST['content'] : null,
            'comments_enabled' => Str::bool($validated_data['comments']),
            'meta'         => !empty($postMeta) ? serialize($postMeta) : null,
        ]);

        if ($post)
        {
            $suffix = $validated_data['status'] === 'published' ? '' : '?draft';

            if ($post->type === 'post')
            {
                $blogPrefix = $this->Config->get('cms.blog_location');

                return ['id' => $post->id, 'slug' => !$blogPrefix ? $post->slug.$suffix : $blogPrefix.'/'.$post->slug.$suffix];
            }

            return ['id' => $post->id, 'slug' => $post->slug.$suffix];
        }

        return false;
    }

    /**
     * Sorts and organises the post meta
     *
     * @access private
     * @return array|false
     */
    private function getPostMeta()
    {
        $keys     = [];
        $values   = [];
        $response = [];

        if (isset($_POST['post-meta-keys'][0]))
        {
            $keys = array_map('trim', explode(',', $_POST['post-meta-keys'][0]));
        }
        if (isset($_POST['post-meta-values'][0]))
        {
            # Bugfix if a value contains a comma
            $delimiter = ',';
            $quote     = "'";
            $regex = "(?:[^$delimiter$quote]|[$quote][^$quote]*[$quote])+";
            preg_match_all('/'.str_replace('/', '\\/', $regex).'/', $_POST['post-meta-values'][0], $matches);
            $values = $matches[0];
        }
        if (count($values) !== count($keys))
        {
            return false;
        }

        foreach ($keys as $i => $key)
        {            
            $response[trim($key, "'")] = trim($values[$i], "'");
        }

        return $response;
    }
}
