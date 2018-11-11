<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\cms\query\helpers\Helper;
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
     * @access public
     * @return kanso\cms\wrappers\Media|null
     */
    public function the_attachment()
    {
        $key = $this->parent->helpers['cache']->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->parent->helpers['cache']->has($key))
        {
            return $this->parent->helpers['cache']->get($key);
        }

        if ($this->parent->requestType === 'attachment')
        {
            return $this->parent->helpers['cache']->set($key, $this->container->get('MediaManager')->provider()->byKey('url', $this->parent->attachmentURL, true));
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
        $key = $this->parent->helpers['cache']->key(__FUNCTION__, func_get_args(), func_num_args());

        if ($this->parent->helpers['cache']->has($key))
        {
            return $this->parent->helpers['cache']->get($key);
        }

        return $this->parent->helpers['cache']->set($key, $this->container->get('MediaManager')->provider()->all());
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
            $attachment = $this->container->get('MediaManager')->provider()->byId($id);

            if ($attachment)
            {
                $name   = Str::getAfterLastChar($attachment->url, '/');

                $prefix = !empty($this->parent->blog_location()) ? '/' . $this->parent->blog_location() . '/' : '/';

                return $this->container->get('Request')->environment()->HTTP_HOST . $prefix . 'attachment/' . trim($name, '/') . '/';
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
