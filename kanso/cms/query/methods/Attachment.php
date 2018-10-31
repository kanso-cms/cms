<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

use kanso\framework\utility\Str;

/**
 * CMS Query attachment methods.
 *
 * @author Joe J. Howard
 */
trait Attachment
{
    /**
     * If the request is for an attachment returns an array of that attachment.
     *
     * @access public
     * @return kanso\cms\wrappers\Media|null
     */
    public function the_attachment()
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        if ($this->requestType === 'attachment')
        {
            return $this->cache->set($key, $this->MediaManager->provider()->byKey('url', $this->attachmentURL, true));
        }

        return null;
    }

    /**
     * If the request is for an attachment returns an array of that attachment.
     *
     * @access public
     * @return kanso\cms\wrappers\Media|null
     */
    public function all_the_attachments()
    {
        $key = $this->cache->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->cache->has($key))
        {
            return $this->cache->get($key);
        }

        return $this->cache->set($key, $this->MediaManager->provider()->all());
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
            $attachment = $this->MediaManager->provider()->byId($id);

            if ($attachment)
            {
                $name   = Str::getAfterLastChar($attachment->url, '/');

                $prefix = !empty($this->blog_location()) ? '/' . $this->blog_location() . '/' : '/';

                return $this->Request->environment()->HTTP_HOST . $prefix . 'attachment/' . trim($name, '/') . '/';
            }
        }

        return $this->attachmentURL;
    }

    /**
     * If the request is for an attachment returns the attachment size suffix.
     *
     * @return string|null
     */
    function the_attachment_size()
    {
        return $this->attachmentSize;
    }
}
