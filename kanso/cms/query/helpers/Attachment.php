<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\utility\Str;

/**
 * CMS Query attachment methods.
 *
 * @author Joe J. Howard
 */
class Attachment extends Helper
{
    /**
     * If the request is for an attachment returns an array of that attachment.
     *
     * @return \kanso\cms\wrappers\Media|null
     */
    public function the_attachment()
    {
        $key = $this->parent->helper('cache')->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->parent->helper('cache')->has($key))
        {
            return $this->parent->helper('cache')->get($key);
        }

        if ($this->parent->requestType === 'attachment')
        {
            return $this->parent->helper('cache')->set($key, $this->container->MediaManager->provider()->byKey('url', $this->parent->attachmentURL, true));
        }

        return null;
    }

    /**
     * If the request is for an attachment returns an array of that attachment.
     *
     * @return \kanso\cms\wrappers\Media|null
     */
    public function all_the_attachments()
    {
        $key = $this->parent->helper('cache')->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->parent->helper('cache')->has($key))
        {
            return $this->parent->helper('cache')->get($key);
        }

        return $this->parent->helper('cache')->set($key, $this->container->MediaManager->provider()->all());
    }

    /**
     * If the request is for an attachment returns the attachment URL.
     *
     * @return string|null
     */
    public function the_attachment_url(int $id = null)
    {
        if ($id)
        {
            $attachment = $this->container->MediaManager->provider()->byId($id);

            if ($attachment)
            {
                $name   = Str::getAfterLastChar($attachment->url, '/');

                $prefix = !empty($this->parent->blog_location()) ? '/' . $this->parent->blog_location() . '/' : '/';

                return $this->container->Request->environment()->HTTP_HOST . $prefix . 'attachment/' . trim($name, '/') . '/';
            }
        }

        return $this->parent->attachmentURL;
    }

    /**
     * If the request is for an attachment returns the attachment size suffix.
     *
     * @return string|null
     */
    public function the_attachment_size()
    {
        return $this->parent->attachmentSize;
    }
}
