<?php

namespace Kanso\Admin\Models;

/**
 * POST Model for ajax requests
 *
 * This model is responsible for validating and parsing
 * POST requests made to the admin panel via Ajax for the writer application
 *
 * The class is instantiated by the respective controller
 */
class WriterAjax
{

    /**
     * @var $_POST
     */
    private $postVars;

    /**
     * @var $_POST['ajax_request']
     */
    private $request;

    /**
     * @var \Kanso\Utility\GUMP
     */
    private $validation;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        # Set the $_POST variables
        $this->postVars   = \Kanso\Kanso::getInstance()->Request->fetch();

        # Set the validation
        $this->validation = \Kanso\Kanso::getInstance()->Validation;

        # Set the request type
        if (isset($this->postVars['ajax_request'])) {
            $this->request = $this->postVars['ajax_request'];
        }
    }

    /**
     * Dispatch the request
     *
     * This method is called from the controller 
     *
     */
    public function dispatch()
    {
        if ($this->request === 'writer_image_upload') {
            return $this->writerImage();
        }
        else if ($this->request === 'writer_publish_article') {
            return $this->publishArticle();
        } 
        else if ($this->request === 'writer_save_existing_article') {
            return $this->saveExistingArticle();
        }
        else if ($this->request === 'writer_save_new_article') {
            return $this->saveNewArticle();
        }
    }

    /**
     * Publish an article
     *
     * @return array|false
     *
     */
    private function publishArticle()
    {
        $this->postVars['status'] = 'published';
        if (isset($this->postVars['id']) && !empty($this->postVars['id']) && $this->postVars['id'] !== 'null') {
            return $this->saveExistingArticle();
        }
        else {
            return $this->saveNewArticle();
        }
    }

    /**
     * Save an existing article
     *
     * @return array|false
     *
     */
    private function saveExistingArticle() 
    {
        # Sanitize and validate the POST variables
        $postVars = $this->validation->sanitize($this->postVars);

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

        $validated_data = $this->validation->run($postVars);
        if (!$validated_data) return false;

        # If the article doesn't exist save a new one
        $article = \Kanso\Kanso::getInstance()->Bookkeeper->existing($validated_data['id']);
        if (!$article) return $this->saveNewArticle();

        $article->title         = $validated_data['title'];
        $article->category      = $validated_data['category'];
        $article->tags          = $validated_data['tags'];
        $article->excerpt       = $validated_data['excerpt'];
        $article->thumbnail_id  = intval($validated_data['thumbnail_id']);
        $article->type          = $validated_data['type'];
        $article->author        = \Kanso\Kanso::getInstance()->Session->get('id');
        $article->comments_enabled = \Kanso\Utility\Str::bool($validated_data['comments']);
        if (isset($_POST['content'])) $article->content = $_POST['content'];
        if (isset($validated_data['status'])) $article->status = $validated_data['status'];

        # If no excerpt was set use the content
        if (empty($validated_data['excerpt'])) {
            $article->excerpt = \Kanso\Utility\Str::reduce($validated_data['content'], 255);
        }

        $save = $article->save();

        if ($save) return ['id' => $article->id, 'slug' => $article->slug];

        return false;
    }

    /**
     * Save a new article
     *
     * @return array|false
     *
     */
    private function saveNewArticle()
    {
        # Sanitize and validate the POST variables
        $postVars = $this->validation->sanitize($this->postVars);

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

        $validated_data = $this->validation->run($postVars);
        if (!$validated_data) return false;

        # Create a new article
        $article = \Kanso\Kanso::getInstance()->Bookkeeper->create();

        # Get the article content directly from the _POST global
        # so it is not filtered in any way
        if (isset($_POST['content'])) $validated_data['content'] = $_POST['content'];


        $article->title         = $validated_data['title'];
        $article->category      = $validated_data['category'];
        $article->tags          = $validated_data['tags'];
        $article->excerpt       = $validated_data['excerpt'];
        $article->thumbnail_id  = $validated_data['thumbnail_id'];
        $article->status        = 'draft';
        $article->type          = $validated_data['type'];
        $article->author        = \Kanso\Kanso::getInstance()->Session->get('id');
        $article->comments_enabled = \Kanso\Utility\Str::bool($validated_data['comments']);
        if (isset($_POST['content'])) $article->content = $_POST['content'];
        if (isset($validated_data['status'])) $article->status = $validated_data['status'];

        # If no excerpt was set use the content
        if (empty($validated_data['excerpt'])) {
            $article->excerpt = \Kanso\Utility\Str::reduce($validated_data['content'], 255);
        }

        $save = $article->save();

        if ($save) return ['id' => $article->id, 'slug' => $article->slug];

        return false;
    }

    /**
     * Upload and image
     *
     * @return string|false
     *
     */
    private function writerImage()
    {
        # Validate a file was sent
        if (!isset($_FILES['file'])) return false;

        # Upload and insert
        $attachment = \Kanso\Kanso::getInstance()->MediaLibrary->upload($_FILES['file']);

        # Validate
        if ($attachment === \Kanso\Media\Attachment::CORRUPT_FILE || !$attachment) {
            return 'corrupt_file';
        }
        else if ($attachment === \Kanso\Media\Attachment::UNSUPPORTED_TYPE) {
            return 'unsupported_file';
        }
        else if (is_a($attachment, 'Kanso\Media\Attachment')) {
            return $attachment->url;
        }
        return false;

    }

}