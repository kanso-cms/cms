<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\methods;

/**
 * CMS Query attachment methods
 *
 * @author Joe J. Howard
 */
trait Attachment
{
    /**
     * If the request is for an attachment returns an array of that attachment
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
     * If the request is for an attachment returns the attachment URL
     *
     * @return string|null 
     */
    public function the_attachment_url() 
    {
        return $this->attachmentURL;
    }

    /**
     * If the request is for an attachment returns the attachment size suffix
     *
     * @return string|null 
     */
    function the_attachment_size() 
    {
        return $this->attachmentSize;
    }
}
