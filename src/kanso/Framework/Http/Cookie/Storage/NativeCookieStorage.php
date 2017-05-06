<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Http\Cookie\Storage;

use Kanso\Framework\Http\Cookie\Storage\StoreInterface;
use Kanso\Framework\Security\Crypto;

/**
 * Cookie encrypt/decrypt
 *
 * @author Joe J. Howard
 */
class NativeCookieStorage implements StoreInterface
{
    /**
     * Encryption service
     *
     * @var Kanso\Framework\Security\Crypto
     */
    private $crypto;

    /**
     * Cookie configuration
     *
     * @var array
     */
    private $configuration;

    /**
     * Constructor
     *
     * @access public
     * @param  \Kanso\Framework\Security\Crypto $Crypto        Encryption service
     * @param  array                            $configuration Assoc array of cookie configurations
     */
    public function __construct(Crypto $crypto, array $configuration)
    {
        $this->crypto = $crypto;

        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $name)
    {
        if (!isset($_COOKIE[$name]))
        {
            return false;
        }

        return unserialize($this->crypto->decrypt($_COOKIE[$name]));
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $name, $data)
    {
        setcookie($name, $this->crypto->encrypt(serialize($data)), $this->configuration['expire'], $this->configuration['path'], $this->configuration['domain'], $this->configuration['secure'], $this->configuration['httponly']);
    }
}
