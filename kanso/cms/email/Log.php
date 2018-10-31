<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\email;

use kanso\framework\file\Filesystem;
use kanso\framework\utility\UUID;

/**
 * Email logging utility.
 *
 * @author Joe J. Howard
 */
class Log
{
    /**
     * Filesystem instance.
     *
     * @var \kanso\framework\file\Filesystem
     */
    private $filesystem;

    /**
     * Path to store logs in.
     *
     * @var string
     */
    private $path;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\file\Filesystem $filesystem Filesystem instance
     * @param string                           $logDir     Directory to log files
     */
    public function __construct(Filesystem $filesystem, string $path)
    {
        $this->path = $path;

        $this->filesystem = $filesystem;
    }

    /**
     * Write email to log.
     *
     * @access public
     * @param  string $emailTo     The email address to send the email to
     * @param  string $senderName  The name of the sender
     * @param  string $senderEmail The email address of the sender
     * @param  string $subject     The subject of the email
     * @param  string $content     The message to be sent
     * @param  string $format      html or plain
     * @return bool
     */
    public function save(string $toEmail, string $senderName, string $senderEmail, string $subject, string $content, string $format)
    {
        $id = UUID::v4();

        $data =
        [
            'to_email'   => $toEmail,
            'from_email' => $senderEmail,
            'from_name'  => $senderName,
            'subject'    => $subject,
            'format'     => $format,
            'date'       => time(),
        ];

        $this->filesystem->putContents($this->path . DIRECTORY_SEPARATOR . $id, serialize($data));

        $this->filesystem->putContents($this->path . DIRECTORY_SEPARATOR . $id . '_content', $content);
    }
}
