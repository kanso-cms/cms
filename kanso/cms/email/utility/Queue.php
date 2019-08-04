<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\email\utility;

use kanso\framework\file\Filesystem;

/**
 * CMS email queue.
 *
 * @author Joe J. Howard
 */
class Queue
{
    /**
     * Filesystem instance.
     *
     * @var \kanso\framework\file\Filesystem
     */
    private $filesystem;

    /**
     * Sender utility instace.
     *
     * @var \kanso\cms\email\utility\Sender
     */
    private $sender;

    /**
     * Mail queue directory.
     *
     * @var string
     */
    private $logDir;

    /**
     * Mail queue file.
     *
     * @var string
     */
    private $queueFile = 'queue.txt';

    /**
     * Is queuing enabled?
     *
     * @var bool
     */
    private $enabled;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\file\Filesystem $filesystem Filesystem instance
     * @param \kanso\cms\email\utility\Sender  $sender     Email sender instance
     * @param string                           $logDir     Log directory
     * @param bool                             $enabled    Is email queuing enabled? (optional) (default false)
     */
    public function __construct(Filesystem $filesystem, Sender $sender, string $logDir, bool $enabled = false)
    {
        $this->filesystem = $filesystem;

        $this->sender = $sender;

        $this->logDir  = $logDir;

        $this->queueFile = $logDir . DIRECTORY_SEPARATOR . $this->queueFile;

        $this->enabled = $enabled;
    }

    /**
     * Is email queuing enabled ?
     *
     * @access public
     * @return bool
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Is email queuing disabled ?
     *
     * @access public
     * @return bool
     */
    public function disabled(): bool
    {
        return !$this->enabled;
    }

    /**
     * Enable email queuing.
     *
     * @access public
     */
    public function enable()
    {
        return $this->enabled = true;
    }

    /**
     * Disable email queuing.
     *
     * @access public
     */
    public function disable()
    {
        return $this->enabled = false;
    }

    /**
     * Add an email to the queue.
     *
     * @access public
     * @param string $id The email id from the log
     */
    public function add(string $id)
    {
        if (!$this->filesystem->exists($this->queueFile))
        {
            $this->filesystem->touch($this->queueFile);
        }

        $this->filesystem->prependContents($this->queueFile, $id . "\n");
    }

    /**
     * Get the email queue as an array.
     *
     * @access public
     * @return array
     */
    public function get(): array
    {
        $queue = [];

        if ($this->filesystem->exists($this->queueFile))
        {
            $_queue = $this->filesystem->getContents($this->queueFile);

            if ($_queue)
            {
                $queue = array_values(array_filter(array_map('trim', explode("\n", $_queue))));
            }
        }

        return $queue;
    }

    /**
     * Process the email queue.
     *
     * @access public
     */
    public function process()
    {
        $queue = $this->get();

        foreach ($queue as $i => $id)
        {
            $infoPath = $this->logDir . DIRECTORY_SEPARATOR . $id;

            $contentsPath = $this->logDir . DIRECTORY_SEPARATOR . $id . '_content';

            if ($this->filesystem->exists($infoPath) && $this->filesystem->exists($contentsPath))
            {
                $info    = unserialize($this->filesystem->getContents($infoPath));
                $content = $this->filesystem->getContents($contentsPath);

                if ($this->sender->send($info['to_email'], $info['from_name'], $info['from_email'], $info['subject'], $content, $info['format']))
                {
                    unset($queue[$i]);

                    $this->filesystem->putContents($this->queueFile, implode("\n", $queue));
                }

            }
        }
    }
}
