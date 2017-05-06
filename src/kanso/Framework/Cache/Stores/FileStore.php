<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Cache\Stores;

use Kanso\Framework\Cache\Stores\StoreInterface;

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
     * {@inheritdoc}
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        if ($this->has($key))
        {
            return file_get_contents($this->keyToFile($key));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, string $data)
    {
        file_put_contents($this->keyToFile($key), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return file_exists($this->keyToFile($key));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key)
    {
        if ($this->has($key))
        {
            unlink($this->keyToFile($key));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function expired(string $key, int $maxAge): bool
    {
        if ($this->has($key))
        {
            if ((($maxAge - time()) + filemtime($this->keyToFile($key))) < time())
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
        $files = glob($this->path.DIRECTORY_SEPARATOR.'*');

        foreach ($files as $file)
        { 
            if (is_file($file))
            {
                unlink($file);
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