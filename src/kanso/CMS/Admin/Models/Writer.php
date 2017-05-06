<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Admin\Models;

use Kanso\Kanso;
use Kanso\Framework\Utility\Str;
use Kanso\CMS\Admin\Models\Model;
use Kanso\CMS\Wrappers\Managers\PostManager;

/**
 * Comments page model
 *
 * @author Joe J. Howard
 */
class Writer extends Model
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

        return false;
    }

    /**
     * Returns the tag manager
     *
     * @access private
     * @return \Kanso\CMS\Wrappers\Managers\PostManager
     */
    private function postManager(): PostManager
    {
        return Kanso::instance()->PostManager;
    }

    /**
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        $queries   = $this->request->queries();
        $post      = false;
        if (isset($queries['id']))
        {
            $post = $this->postManager()->byId(intval($queries['id']));
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
        ]);

        $validated_data = $this->validation->run($post);
        
        if (!$validated_data)
        {
            return false;
        }

        $validated_data['id'] = intval($validated_data['id']);
        
        $article = $this->postManager()->byId($validated_data['id']);
        
        if (!$article)
        {
            return false;
        }

        $article->title            = $validated_data['title'];
        $article->category         = $validated_data['category'];
        $article->tags             = $validated_data['tags'];
        $article->excerpt          = $validated_data['excerpt'];
        $article->type             = $validated_data['type'];
        $article->author_id        = $this->gatekeeper->getUser()->id;
        $article->comments_enabled = Str::bool($validated_data['comments']);

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
            $article->excerpt = Str::reduce($validated_data['content'], 255);
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
            return ['id' => $article->id, 'slug' => $article->slug];
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

        $post = $this->postManager()->create([
            'title'        => $validated_data['title'],
            'category'     => $validated_data['category'],
            'tags'         => $validated_data['tags'],
            'excerpt'      => empty($validated_data['excerpt']) ? Str::reduce($validated_data['content'], 255) : Str::reduce($validated_data['excerpt'], 255),
            'thumbnail_id' => $validated_data['thumbnail_id'],
            'status'       => 'draft',
            'type'         => $validated_data['type'],
            'author_id'    => $this->gatekeeper->getUser()->id,
            'content'      => !empty($_POST['content']) ? $_POST['content'] : null,
            'comments_enabled' => Str::bool($validated_data['comments']),
        ]);

        if ($post)
        {
            return ['id' => $post->id, 'slug' => $post->slug];
        }

        return false;
    }
}
