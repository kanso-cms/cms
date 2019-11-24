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
     * @var string|null
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
     * @param \kanso\framework\security\Crypto $crypto       Crypto instance
     * @param \kanso\framework\file\Filesystem $filesystem   Filesystem instance
     * @param array                            $cookieParams Assoc array of cookie configurations
     * @param string|null                      $storageDir   Path to save session files to (optional) (default null)
     */
    public function __construct(Crypto $crypto, Filesystem $filesystem, array $cookieParams = [], string $storageDir = null)
    {
        $this->crypto = $crypto;

        $this->filesystem = $filesystem;

        $this->session_set_cookie_params($cookieParams);

        $this->session_save_path($storageDir);

        $this->session_gc();
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
    public function session_start(): void
    {
        if ($this->sent || $this->started)
        {
            return;
        }

        $this->started = true;

        if (!isset($_COOKIE[$this->session_name]))
        {
            $this->generateId();
        }
        else
        {
            $this->id = $this->crypto->decrypt($_COOKIE[$this->session_name]);

            if (!$this->sessionFileExists() || !UUID::validate($this->id))
            {
                $this->generateId();
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function session_destroy(): void
    {
        if ($this->started && !$this->sent)
        {
            $this->filesystem->delete($this->sessionFile());

            $this->id = null;

            $this->started = false;

            unset($_COOKIE[$this->session_name]);
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
    public function session_regenerate_id(bool $deleteOldSession = false): void
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
    }

    /**
     * {@inheritdoc}
     */
    public function session_set_cookie_params(array $params): void
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

        if (mt_rand(1, 100) === 100)
        {
            // Max age in hours of now until a session expires
            $maxAge = abs($this->cookieParams['expire'] - time()) / 3600;

            $files = scandir($this->storageDir);

            foreach ($files as $file)
            {
                if ($file === '.' || $file === '..' || $file[0] === '.')
                {
                    continue;
                }

                $realPath = $this->storageDir . DIRECTORY_SEPARATOR . $file;

                if (strtotime('+' . $maxAge . ' hours', $this->filesystem->lastModified($realPath)) < time())
                {
                    $this->filesystem->delete($realPath);

                    $deleted = true;
                }
            }
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
    public function write(array $data): void
    {
        if ($this->started)
        {
            $this->filesystem->putContents($this->sessionFile(), serialize($data));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send(): void
    {
        if ($this->started && !$this->sent)
        {
            setcookie($this->session_name, $this->crypto->encrypt($this->id), $this->cookieParams['expire'], $this->cookieParams['path'], $this->cookieParams['domain'], $this->cookieParams['secure'], $this->cookieParams['httponly']);

            $this->sent = true;
        }
    }

    /**
     * Get the path to the current session file.
     *
     * @return string|false
     */
    private function sessionFile()
    {
        return !empty($this->id) ? $this->storageDir . DIRECTORY_SEPARATOR . $this->id : false;
    }

    /**
     * Generate a session id.
     */
    private function generateId(): void
    {
        $this->id = UUID::v4();
    }

    /**
     * Get the path to the current session file.
     *
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
}
