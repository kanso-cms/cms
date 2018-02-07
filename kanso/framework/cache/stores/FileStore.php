<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cache\stores;

use kanso\framework\cache\stores\StoreInterface;
use kanso\framework\file\Filesystem;

/**
 * Cache file storage
 *
 * @author Joe J. Howard
 */
Class FileStore implements StoreInterface
{
    /**
     * {@inheritdoc}
     */
    private $path;

    /**
     * Filesystem instance
     * 
     * @var \kanso\framework\file\Filesystem
     */
    private $filesystem;

    /**
     * Constructo
     * 
     * @param \kanso\framework\file\Filesystem $filesystem Filesystem instance
     * @param string                           $path       Directory to store cache files
     */
    public function __construct(Filesystem $filesystem, string $path)
    {
        $this->path = $path;

        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        if ($this->has($key))
        {
            return $this->filesystem->getContents($this->keyToFile($key));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, string $data)
    {
        $this->filesystem->putContents($this->keyToFile($key), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $this->filesystem->exists($this->keyToFile($key));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key)
    {
        if ($this->has($key))
        {
            $this->filesystem->delete($this->keyToFile($key));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function expired(string $key, int $maxAge): bool
    {
        if ($this->has($key))
        {
            if ((($maxAge - time()) + $this->filesystem->lastModified($this->keyToFile($key))) < time())
            {
                return true;
            }
        }
        
        return false;        
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $files = $this->filesystem->list($this->path);

        foreach ($files as $file)
        { 
            $path = $this->path . DIRECTORY_SEPARATOR . $file;

            if ($this->filesystem->exists($path))
            {
                $this->filesystem->delete($path);
            }
        }
    }

    /**
     * Converts a key to the file path
     *
     * @access public
     * @param  string $key Key to convert
     */
    private function keyToFile(string $key): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $key . '.cache';
    }
}