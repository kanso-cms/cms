<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session\storage;

use kanso\framework\http\session\storage\StoreInterface;
use kanso\framework\utility\UUID;
use kanso\framework\security\Crypto;

/**
 * Session encrypt/decrypt
 *
 * @author Joe J. Howard
 */
class FileSessionStorage implements StoreInterface
{
    /**
     * Current session id
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
     * Has the garbage been collected
     *
     * @var bool
     */
    private $garbageColltected = false;

    /**
     * Session cookie parameters
     *
     * @var array
     */
    private $cookieParams = [];

    /**
     * Where to save the files to
     *
     * @var string
     */
    private $storageDir = '';

    /**
     * Session cookie name
     *
     * @var string
     */
    private $session_name = 'kanso_session';

    /**
     * Name of the garbage collection file
     *
     * @var string
     */
    private $GCFileName = 'php_session_last_gc';

    /**
     * Garbage collection frequency
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
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\security\Crypto $Crypto        Encryption service
     * @param  array                            $cookieParams  Assoc array of cookie configurations
     * @param  string                           $storageDir    Path to save session files to (optional) (default null)
     */
    public function __construct(Crypto $crypto, array $cookieParams = [], string $storageDir = null)
    {
        $this->crypto = $crypto;

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
        if ($this->started && isset($_COOKIE) && !$this->sent)
        {
            unset($_COOKIE[$this->session_name()]);

            $this->started = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function session_id(string $id = null)
    {
        if ($id)
        {
            $this->id = $id;

            $_COOKIE[$this->session_name()] =  $this->crypto->encrypt($this->id);
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
                rename($this->sessionFile(), $this->storageDir.DIRECTORY_SEPARATOR.$newId);
            }
        }
        else
        {
            if ($this->sessionFileExists())
            {
                file_put_contents($this->storageDir.DIRECTORY_SEPARATOR.$newId, file_get_contents($this->sessionFile()));
            }
        }

        $this->id = $newId;
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
            $gc_time = $this->storageDir.DIRECTORY_SEPARATOR.$this->GCFileName;

            if (file_exists($gc_time))
            {
                if (filemtime($gc_time) < time() - $this->gCPeriod)
                {
                    $deleted = $this->deleteOldSessions();

                    touch($gc_time);
                }
            }
            else
            {
                touch($gc_time);
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
            return unserialize(file_get_contents($this->sessionFile()));
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
            file_put_contents($this->sessionFile(), serialize($data));
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
     * Get the path to the current session file
     *
     * @access private
     * @return string|false
     */
    private function sessionFile()
    {
        if (!empty($this->id))
        {
            return $this->storageDir.DIRECTORY_SEPARATOR.$this->id;
        }

        return false;
    }

    /**
     * Get the path to the current session file
     *
     * @access private
     * @return bool
     */
    private function sessionFileExists(): bool
    {
        $path = $this->sessionFile();

        if ($path)
        {
            return is_file($path) && file_exists($path);
        }

        return false;
    }

    /**
     * Delete old session files
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

            # Sessions more than 12 hours are deleted
            if ( time() - filemtime($this->storageDir.DIRECTORY_SEPARATOR.$file) > 86400)
            {
                unlink($this->storageDir.DIRECTORY_SEPARATOR.$file);

                $deleted++;
            }
        }

        return $deleted;
    }
}
