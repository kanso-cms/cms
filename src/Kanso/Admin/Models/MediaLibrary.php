<?php

namespace Kanso\Admin\Models;

/**
 * POST Model for Media Library ajax requests
 *
 * This model is responsible for validating and parsing
 * POST requests made to the admin panel via Ajax for the media library
 *
 * The class is instantiated by the respective controller
 */
class MediaLibrary
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
     * @var \Kanso\Kanso::getInstance()->Database->Builder()
     */
    private $SQL;

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

        # Get an SQL builder
        $this->SQL = \Kanso\Kanso::getInstance()->Database->Builder();

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
        if ($this->request === 'load_images') {
            return $this->loadImages();
        }
        else if ($this->request === 'delete_images') {
            return $this->deleteImages();
        } 
        else if ($this->request === 'update_image_info') {
            return $this->updateImageInfo();
        }
         else if ($this->request === 'file_upload') {
            return $this->uploadMedia();
        }
    }

    /**
     * Load the images
     *
     * @return array|false
     *
     */
    private function loadImages()
    {
        # Page must be set
        if (!isset($this->postVars['page'])) return false;

        # Query vars
        $page     = intval($this->postVars['page']);
        $perPage  = 30;
        $offset   = $page * $perPage;
        $limit    = $perPage;

        # Select the images
        $response     = [];
        $rows         = $this->SQL->SELECT('*')->FROM('media_uploads')->ORDER_BY('date', 'DESC')->LIMIT($offset, $limit)->FIND_ALL();
        $filestsystem = \Kanso\Kanso::getInstance()->FileSystem;
        $imageTypes   = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];

        foreach ($rows as $image) {
            if (!$filestsystem->exists($image['path'])) continue;
            $image['date']   = \Kanso\Utility\Humanizer::timeAgo($image['date']).' ago';
            $image['size']   = \Kanso\Utility\Humanizer::fileSize($image['size']);
            $image['user']   = $this->SQL->SELECT('name')->FROM('users')->WHERE('id', '=', $image['uploader_id'])->ROW()['name'];
            $image['type']   = $filestsystem->mime($image['path']);
            $image['preview'] = $image['url'];
            $image['name']   = \Kanso\Utility\Str::getAfterLastChar($image['url'], '/');

            # If this is not an image put no preview on it
            if (!in_array($image['type'], $imageTypes)) {
                $image['preview'] = \Kanso\Kanso::getInstance()->Environment['KANSO_IMGS_URL'].'/no-preview-available.jpg';
            }

            $response[] = $image;
        }

        return $response;    
    }

    /**
     * Delete images
     *
     * @return array|false
     *
     */
    private function deleteImages()
    {

        # Validate and sanitize the ids
        if (!isset($this->postVars['ids'])) return false;
        $ids = array_filter(array_map('intval', explode(',', $this->postVars['ids'])));
        if (empty($ids)) return false;

        foreach ($ids as $id) {
            $attachment = \Kanso\Kanso::getInstance()->MediaLibrary->byId($id);
            $attachment->delete();
        }

        return 'valid';
    }

    /**
     * Update image info
     *
     * @return array|false
     *
     */
    private function updateImageInfo()
    {

        # Validate and sanitize the id
        if (!isset($this->postVars['id'])) return false;
        $id = intval($this->postVars['id']);
        if (empty($id)) return false;

        # Validate the post vars
        if (!array_key_exists('title', $this->postVars) || !array_key_exists('rel', $this->postVars) || !array_key_exists('alt', $this->postVars)) {
            return false;
        }

        # Get the attachment
        $attachment = \Kanso\Kanso::getInstance()->MediaLibrary->byId($id);
        if (!$attachment) return false;

        $attachment->title = trim($this->postVars['title']);
        $attachment->rel   = trim($this->postVars['rel']);
        $attachment->alt   = trim($this->postVars['alt']);
        $attachment->save();

        return 'valid';
    }

    /**
     * Upload files
     *
     * @return array|false
     *
     */
    private function uploadMedia()
    {
        # Validate files
        if (empty($_FILES) || !isset($_FILES['file'])) return false;
        if (!isset($_FILES['file']['name']) || !isset($_FILES['file']['type']) || !isset($_FILES['file']['tmp_name']) || !isset($_FILES['file']['size'])) {
            return false;
        }

        # Loop keys and separate into individual file arrays
        $files = [];
        foreach ($_FILES['file'] as $key => $values) {
            if (is_array($values)) {
                foreach ($values as $i => $value) {
                    $files[$i][$key] = $value;
                }
            }
            else {
                $files[0][$key] = $values;
            }
            
        }

        # Upload and prepare the repsonse
        $imageTypes   = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
        $filesystem   = \Kanso\Kanso::getInstance()->FileSystem;
        $uploaded     = [];
        foreach ($files as $file) {
            $attachment = \Kanso\Kanso::getInstance()->MediaLibrary->upload($file);

            if (is_a($attachment, 'Kanso\Media\Attachment')) {
                $image = $attachment->asArray();
                $image['date']    = \Kanso\Utility\Humanizer::timeAgo($image['date']).' ago';
                $image['size']    = \Kanso\Utility\Humanizer::fileSize($image['size']);
                $image['user']    = $this->SQL->SELECT('name')->FROM('users')->WHERE('id', '=', $image['uploader_id'])->ROW()['name'];
                $image['type']    = $filesystem->mime($image['path']);
                $image['preview'] = $image['url'];
                $image['name']    = \Kanso\Utility\Str::getAfterLastChar($image['url'], '/');

                # If this is not an image put no preview on it
                if (!in_array($image['type'], $imageTypes)) {
                    $image['preview'] = \Kanso\Kanso::getInstance()->Environment['KANSO_IMGS_URL'].'no-preview-available.jpg';
                }
                $uploaded[] = $image;
            }
        }

        return $uploaded;

    }

}