<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

use kanso\framework\utility\Mime;

/**
 * Response format.
 *
 * @author Joe J. Howard
 */
class Format
{
    /**
     * Mime type for response.
     *
     * @var string
     */
    protected $type;

    /**
     * Encoding for response.
     *
     * @var string
     */
    protected $encoding;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Set the mime type.
     *
     * @param string $type Mime type or extension of file format
     */
    public function set(string $type): void
    {
        $fromExt = Mime::fromExt($type);

        if ($fromExt)
        {
            $this->type = $fromExt;
        }
        else
        {
            $this->type = $type;
        }
    }

    /**
     * Set the mime type.
     *
     * @param string $encoding A valid encoding format
     * @see    http://php.net/manual/en/mbstring.supported-encodings.php
     */
    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    /**
     * Get the mime type.
     *
     * @return string
     */
    public function get()
    {
       return $this->type;
    }

    /**
     * Get the encoding.
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
