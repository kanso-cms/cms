<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session\storage;

use kanso\framework\file\Filesystem;
use kanso\framework\security\Crypto;
use kanso\framework\utility\UUID;
use RuntimeException;

/**
 * Session encrypt/decrypt.
 *
 * @author Joe J. Howard
 */
class FileSessionStorage implements StoreInterface
{
    /**
     * Current session id.
     *
     * @var string
     */
    private $id = '';

    /**
     * Has the session already been started ?
     *
     * @var bool
     */
    private $started = false;

    /**
     * Has the session already been sent ?
     *
     * @var bool
     */
    private $sent = false;

    /**
     * Has the garbage been collected.
     *
     * @var bool
     */
    private $garbageColltected = false;

    /**
     * Session cookie parameters.
     *
     * @var array
     */
    private $cookieParams = [];

    /**
     * Where to save the files to.
     *
     * @var string
     */
    private $storageDir = '';

    /**
     * Session cookie name.
     *
     * @var string
     */
    private $session_name = 'kanso_session';

    /**
     * Name of the garbage collection file.
     *
     * @var string
     */
    private $GCFileName = 'php_session_last_gc';

    /**
     * Garbage collection frequency.
     *
     * @var int
     */
    private $gCPeriod = 1800;

    /**
     * Is this a HTTP request ?
     *
     * @var bool
     */
    private $isHttpRequest = false;

    /**
     * Filesystem instance.
     *
     * @var \kanso\framework\file\Filesystem
     */
    private $filesystem;

    /**
     * Crypto instance.
     *
     * @var \kanso\framework\security\Crypto
     */
    private $crypto;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\security\Crypto $crypto       Crypto instance
     * @param \kanso\framework\file\Filesystem $filesystem   Filesystem instance
     * @param array                            $cookieParams Assoc array of cookie configurations
     * @param string                           $storageDir   Path to save session files to (optional) (default null)
     */
    public function __construct(Crypto $crypto, Filesystem $filesystem, array $cookieParams = [], string $storageDir = null)
    {
        $this->crypto = $crypto;

        $this->filesystem = $filesystem;

        $this->session_set_cookie_params($cookieParams);

        $this->session_save_path($storageDir);

        if (isset($_COOKIE))
        {
            $this->isHttpRequest = true;
        }
        else
        {
            $this->isHttpRequest = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function session_save_path(string $path = '')
    {
        if ($path)
        {
            $this->storageDir = $path;
        }
        else
        {
            if (!$this->storageDir)
            {
                $this->storageDir = sys_get_temp_dir();
            }
        }

        return $this->storageDir;
    }

    /**
     * {@inheritdoc}
     */
    public function session_start()
    {
        if ($this->sent === true)
        {
            return;
        }

        if (!$this->isHttpRequest && $this->cookieParams['httponly'] === true)
        {
            return;
        }

        if (!$this->started)
        {
            $this->started = true;

            if (!$this->garbageColltected)
            {
                $this->session_gc();
            }

            if (!isset($_COOKIE[$this->session_name()]))
            {
                $this->session_regenerate_id();
            }
            else
            {
                $this->id = $this->crypto->decrypt($_COOKIE[$this->session_name()]);

                if (!$this->sessionFileExists() || !UUID::validate($this->id))
                {
                    $this->session_regenerate_id(true);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function session_destroy()
    {
        if ($this->started && !$this->sent)
        {
            $this->filesystem->delete($this->sessionFile());

            $this->id = null;

            $this->started = false;

            unset($_COOKIE[$this->session_name()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function session_id(string $id = null)
    {
        if ($id)
        {
            if ($this->started)
            {
                throw new RuntimeException('Error replacing session id. This method must be called before "session_start()" is called.');
            }

            if (!UUID::validate($id))
            {
                throw new RuntimeException('Error replacing session id. The provided id [' . $id . '] is not a valid UUID.');
            }

            $this->id = $id;

            $_COOKIE[$this->session_name()] = $this->crypto->encrypt($this->id);

        }

        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function session_name(string $name = null)
    {
        if ($name)
        {
            if ($this->started)
            {
                throw new RuntimeException('Error replacing session name. This method must be called before "session_start()" is called.');
            }

            $this->session_name = $name;
        }

        return $this->session_name;
    }

    /**
     * {@inheritdoc}
     */
    public function session_regenerate_id(bool $deleteOldSession = false)
    {
        $newId = UUID::v4();

        if ($deleteOldSession)
        {
            if ($this->sessionFileExists())
            {
                $this->filesystem->rename($this->sessionFile(), $this->storageDir . DIRECTORY_SEPARATOR . $newId);
            }
        }
        else
        {
            if ($this->sessionFileExists())
            {
                $this->filesystem->putContents($this->storageDir . DIRECTORY_SEPARATOR . $newId, $this->filesystem->getContents($this->sessionFile()));
            }
        }

        $this->id = $newId;

        $_COOKIE[$this->session_name()] = $this->crypto->encrypt($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function session_set_cookie_params(array $params)
    {
        $this->cookieParams = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function session_get_cookie_params()
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function session_gc()
    {
        $deleted = false;

        if (!$this->garbageColltected)
        {
            $gc_time = $this->storageDir . DIRECTORY_SEPARATOR . $this->GCFileName;

            if ($this->filesystem->exists($gc_time))
            {
                if ($this->filesystem->lastModified($gc_time) < time() - $this->gCPeriod)
                {
                    $deleted = $this->deleteOldSessions();

                    $this->filesystem->touch($gc_time);
                }
            }
            else
            {
                $this->filesystem->touch($gc_time);
            }

            $this->garbageColltected = true;
        }

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if ($this->started && $this->sessionFileExists())
        {
            return unserialize($this->filesystem->getContents($this->sessionFile()));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        if ($this->started)
        {
            $this->filesystem->putContents($this->sessionFile(), serialize($data));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send()
    {
        if ($this->started && !$this->sent)
        {
            setcookie($this->session_name(), $this->crypto->encrypt($this->id), $this->cookieParams['expire'], $this->cookieParams['path'], $this->cookieParams['domain'], $this->cookieParams['secure'], $this->cookieParams['httponly']);

            $this->sent = true;
        }
    }

    /**
     * Get the path to the current session file.
     *
     * @access private
     * @return string|false
     */
    private function sessionFile()
    {
        if (!empty($this->id))
        {
            return $this->storageDir . DIRECTORY_SEPARATOR . $this->id;
        }

        return false;
    }

    /**
     * Get the path to the current session file.
     *
     * @access private
     * @return bool
     */
    private function sessionFileExists(): bool
    {
        $path = $this->sessionFile();

        if ($path)
        {
            return $this->filesystem->exists($path);
        }

        return false;
    }

    /**
     * Delete old session files.
     *
     * @access private
     * @return array
     */
    private function deleteOldSessions(): int
    {
        $deleted = 0;

        $files = scandir($this->storageDir);

        foreach ($files as $file)
        {
            if ($file === '.' || $file === '..' || $file[0] === '.' || $file === $this->GCFileName)
            {
                continue;
            }

            // Sessions more than 12 hours are deleted
            if (time() - filemtime($this->storageDir . DIRECTORY_SEPARATOR . $file) > 86400)
            {
                $this->filesystem->delete($this->storageDir . DIRECTORY_SEPARATOR . $file);

                $deleted++;
            }
        }

        return $deleted;
    }
}
