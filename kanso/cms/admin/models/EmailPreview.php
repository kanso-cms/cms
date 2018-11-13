<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\utility\Str;

/**
 * Admin panel sent email preview.
 *
 * @author Joe J. Howard
 */
class EmailPreview extends BaseModel
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
        // Process any AJAX requests here
        //
        // Returning an associative array will
        // send a JSON response to the client

        // Returning false sends a 404
        return false;
    }

    /**
     * Parse and validate the incoming get request.
     *
     * @return false|array
     */
    public function parseGet()
    {
        $id = explode('/', Str::queryFilterUri($this->Request->environment()->REQUEST_URI));
        $id = array_pop($id);

        $path         = $this->Config->get('email.log_dir');
        $file         = $path . '/' . $id;
        $contentFile  = $path . '/' . $id . '_content';

        if ($this->Filesystem->exists($file) && $this->Filesystem->exists($contentFile))
        {
            $data    = unserialize($this->Filesystem->getContents($file));
            $content = $this->Filesystem->getContents($contentFile);

            if ($data['format'] !== 'html')
            {
                $this->Response->format()->set('txt');
            }

            return ['content' => $content];
        }

        return false;
    }
}
