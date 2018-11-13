<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\utility\Humanizer;
use kanso\framework\utility\Mime;
use kanso\framework\utility\Str;

/**
 * Comments model.
 *
 * @author Joe J. Howard
 */
class MediaLibrary extends BaseModel
{
    /**
     * Default image types.
     *
     * @var array
     */
    private $imageTypes =
    [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/gif',
    ];

    /**
     * SVG Mime Type.
     *
     * @var string
     */
    private $svgMime = 'image/svg+xml';

    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn())
        {
            return [];
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
            return [];
        }

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

            if ($request === 'load_media')
            {
                return $this->loadMedia();
            }
            elseif ($request === 'delete_media')
            {
                return $this->deleteMedia();
            }
            elseif ($request === 'update_media_info')
            {
                return $this->updateMediaInfo();
            }
            elseif ($request=== 'file_upload')
            {
                return $this->uploadMedia();
            }
        }

        throw new RequestException('Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
    }

    /**
     * Load images for the media library.
     *
     * @access private
     * @return array|false
     */
    private function loadMedia()
    {
        // Page must be set
        if (!isset($this->post['page']))
        {
            return false;
        }

        // Query vars
        $page     = intval($this->post['page']);
        $perPage  = 30;
        $offset   = $page * $perPage;
        $limit    = $perPage;

        // Select the images
        $response      = [];
        $rows          = $this->sql()->SELECT('*')->FROM('media_uploads')->ORDER_BY('date', 'DESC')->LIMIT($offset, $limit)->FIND_ALL();
        $imagesBaseURL = str_replace($this->Request->environment()->DOCUMENT_ROOT, $this->Request->environment()->HTTP_HOST, $this->Config->get('cms.uploads.path'));

        foreach ($rows as $image)
        {
            if (!file_exists($image['path'])) continue;
            $image['date']    = Humanizer::timeAgo($image['date']) . ' ago';
            $image['size']    = Humanizer::fileSize($image['size']);
            $image['user']    = $this->sql()->SELECT('name')->FROM('users')->WHERE('id', '=', $image['uploader_id'])->ROW()['name'];
            $image['type']    = Mime::fromExt(Str::getAfterLastChar($image['path'], '.'));
            $image['preview'] = $image['url'];
            $image['name']    = Str::getAfterLastChar($image['url'], '/');

            // If this is not an image put no preview on it
            if (!in_array($image['type'], $this->imageTypes) && $image['type'] !== $this->svgMime)
            {
                $image['preview'] = $imagesBaseURL . '/no-preview-available.jpg';
            }

            $response[] = $image;
        }

        return $response;
    }

    /**
     * Delete images by ids.
     *
     * @access private
     * @return array|false
     */
    private function deleteMedia()
    {
        // Validate and sanitize the ids
        if (!isset($this->post['ids']))
        {
            return false;
        }

        $ids = array_filter(array_map('intval', explode(',', $this->post['ids'])));

        if (empty($ids))
        {
            return false;
        }

        foreach ($ids as $id)
        {
            $attachment = $this->MediaManager->byId($id);

            $attachment->delete();
        }

        return 'valid';
    }

    /**
     * Update an attachment info.
     *
     * @access private
     * @return array|false
     */
    private function updateMediaInfo()
    {
        // Validate and sanitize the id
        if (!isset($this->post['id']))
        {
            return false;
        }

        $id = intval($this->post['id']);

        if (empty($id))
        {
            return false;
        }

        // Validate the post vars
        if (!array_key_exists('title', $this->post) || !array_key_exists('alt', $this->post))
        {
            return false;
        }

        // Get the attachment
        $attachment = $this->MediaManager->byId($id);

        if (!$attachment)
        {
            return false;
        }

        $attachment->title = trim($this->post['title']);
        $attachment->alt   = trim($this->post['alt']);
        $attachment->save();

        return 'valid';
    }

    /**
     * Upload file or files.
     *
     * @access private
     * @return array|false
     */
    private function uploadMedia()
    {
        // Validate files
        if (empty($_FILES) || !isset($_FILES['file']))
        {
            return false;
        }

        if (!isset($_FILES['file']['name']) || !isset($_FILES['file']['type']) || !isset($_FILES['file']['tmp_name']) || !isset($_FILES['file']['size']))
        {
            return false;
        }

        // Loop keys and separate into individual file arrays
        $files = [];
        foreach ($_FILES['file'] as $key => $values)
        {
            if (is_array($values))
            {
                foreach ($values as $i => $value)
                {
                    $files[$i][$key] = $value;
                }
            }
            else
            {
                $files[0][$key] = $values;
            }

        }

        // Upload and prepare the repsonse
        $uploaded      = [];
        $imagesBaseURL = str_replace($this->Request->environment()->DOCUMENT_ROOT, $this->Request->environment()->HTTP_HOST, $this->Config->get('cms.uploads.path'));

        foreach ($files as $file)
        {
            $media = $this->MediaManager->upload($file);

            if ($media)
            {
                $image = $media->asArray();
                $image['date']    = Humanizer::timeAgo($image['date']) . ' ago';
                $image['size']    = Humanizer::fileSize($image['size']);
                $image['user']    = $this->sql()->SELECT('name')->FROM('users')->WHERE('id', '=', $image['uploader_id'])->ROW()['name'];
                $image['type']    = Mime::fromExt(Str::getAfterLastChar($image['path'], '.'));
                $image['preview'] = $image['url'];
                $image['name']    = Str::getAfterLastChar($image['url'], '/');

                // If this is not an image put no preview on it
                if (!in_array($image['type'], $this->imageTypes) && $image['type'] !== $this->svgMime)
                {
                    $image['preview'] = $imagesBaseURL . '/no-preview-available.jpg';
                }

                $uploaded[] = $image;
            }
        }

        return $uploaded;
    }
}
