<?php

namespace Kanso\Admin\Models;

/**
 * POST Model for ajax requests
 *
 * This model is responsible for validating and parsing all
 * POST requests made to the admin panel via Ajax
 *
 * The class is instantiated by the respective controller
 */
class Ajax
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
            'title'      => 'trim|sanitize_string',
            'category'   => 'trim|sanitize_string',
            'tags'       => 'trim|sanitize_string',
            'type'       => 'trim|sanitize_string',
            'excerpt'    => 'trim|sanitize_string',
            'thumbnail'  => 'trim|sanitize_string',
            'status'     => 'trim|sanitize_string',
            'id'         => 'trim|sanitize_numbers',
        ]);

        $validated_data = $this->validation->run($postVars);

        if (!$validated_data) return false;

        $article = \Kanso\Kanso::getInstance()->Bookkeeper->existing($validated_data['id']);

        if (!$article) return $this->saveNewArticle();

        $article->title      = $validated_data['title'];
        $article->category   = $validated_data['category'];
        $article->tags       = $validated_data['tags'];
        $article->excerpt    = $validated_data['excerpt'];
        $article->thumbnail  = $validated_data['thumbnail'];
        $article->type       = $validated_data['type'];
        $article->author     = \Kanso\Kanso::getInstance()->Session->get('id');
        $article->comments_enabled = \Kanso\Utility\Str::bool($validated_data['comments']);
        if (isset($_POST['content'])) $article->content = $_POST['content'];
        if (isset($validated_data['status'])) $article->status = $validated_data['status'];

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
            'title'      => 'trim|sanitize_string',
            'category'   => 'trim|sanitize_string',
            'tags'       => 'trim|sanitize_string',
            'type'       => 'trim|sanitize_string',
            'excerpt'    => 'trim|sanitize_string',
            'thumbnail'  => 'trim|sanitize_string',
            'status'     => 'trim|sanitize_string',
        ]);


        $validated_data = $this->validation->run($postVars);

        if (!$validated_data) return false;

        $article = \Kanso\Kanso::getInstance()->Bookkeeper->create();

        # Get the article content directly from the _POST global
        # so it is not filtered in any way
        if (isset($_POST['content'])) $validated_data['content'] = $_POST['content'];

        if (empty($validated_data['excerpt'])) {
            $validated_data['excerpt'] = \Kanso\Utility\Str::reduce($validated_data['content'], 255);
        }

        $article->title      = $validated_data['title'];
        $article->category   = $validated_data['category'];
        $article->tags       = $validated_data['tags'];
        $article->excerpt    = $validated_data['excerpt'];
        $article->thumbnail  = $validated_data['thumbnail'];
        $article->status     = 'draft';
        $article->type       = $validated_data['type'];
        $article->author     = \Kanso\Kanso::getInstance()->Session->get('id');
        $article->comments_enabled = \Kanso\Utility\Str::bool($validated_data['comments']);
        if (isset($validated_data['status'])) $article->status = $validated_data['status'];


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

        # Validate the file has a mime
        if (!isset($_FILES['file']['type'])) return false;

        # Validate this is an image
        if ($_FILES['file']['type'] !== 'image/png' && $_FILES['file']['type'] !== 'image/jpeg') return false;

        # Convert the mime to an extension
        $mime = \Kanso\Kanso::getInstance()->Request->mimeToExt($_FILES['file']['type']);
        if ($mime !== 'jpg' && $mime !== 'png') return false;

        # Get the environment
        $env = \Kanso\Kanso::getInstance()->Environment;

        # Get the config
        $config = \Kanso\Kanso::getInstance()->Config;

        # Declare size suffixes for resizing
        $sizes  = ["small", "medium", "large"];

        # Grab our image processor
        $Imager = new \Kanso\Utility\Images($_FILES['file']['tmp_name']);

        # Declare config sizes locally
        $configSizes = $config['KANSO_THUMBNAILS'];

        # If this is a author thumbnail crop to square
        $imgurl = '';
        $loop   = 3;

        # Loop through config sizes - maximum is 3 thumbnails
        for ($i=0; $i < count($configSizes) && $i < $loop; $i++) {
            $size  = $configSizes[$i];

            # Sanitize the file name
            $name  = htmlentities(str_replace("/", "", stripslashes($_FILES['file']['name']))); 

            # Get the extension
            $ext   = $mime;

            # Get the name minus the ext
            $name  = explode('.'.$ext, $name)[0];

            # Set the destination and quality
            $dst   = $env['KANSO_UPLOADS_DIR'].'/Images/'.$name.'_'.$sizes[$i].'.'.$ext;
            $qual  = $config['KANSO_IMG_QUALITY'];

            $qual  = ($mime === 'png' ? ($qual/10) : $qual);

            # If sizes are declared with width & Height - resize to those dimensions
            # otherwise just resize to width;
            if (is_array($size)) {
                $Imager->crop($size[0], $size[1], true);
            }
            else {
                $Imager->resizeToWidth($size, true);
            }

            # Save the file
            $saved = $Imager->save($dst, false, $qual);

            if (!$saved) return false;

            $imgurl = str_replace($env['DOCUMENT_ROOT'], $env['HTTP_HOST'], $dst);

        }

        \Kanso\Events::fire('imageUpload', str_replace( $env['HTTP_HOST'], $env['DOCUMENT_ROOT'], $imgurl));

        return $imgurl;

    }

}