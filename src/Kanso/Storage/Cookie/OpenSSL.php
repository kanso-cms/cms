<?php

namespace Kanso\Storage\Cookie;

class OpenSSL
{

    /**
     * Derivation hash.
     *
     * @var string
     */
    
    const DERIVATION_HASH = 'sha256';

    /**
     * Derivation iterations.
     *
     * @var int
     */

    const DERIVATION_ITERATIONS = 1024;

    /**
     * Key used to encrypt/decrypt string.
     *
     * @var string
     */

    protected $key;

    /**
     * The cipher method to use for encryption.
     *
     * @var string
     */

    protected $cipher;

    /**
     * Initialization vector size.
     *
     * @var string
     */

    protected $ivSize;

    /**
     * Constructor.
     *
     * @access  public
     * @param   string  $key     Encryption key
     * @param   int     $cipher  Cipher
     */

    public function __construct($key, $cipher = null)
    {
        $this->key = $key;

        $this->cipher = $cipher ?: 'AES-256-OFB';

        $this->ivSize = openssl_cipher_iv_length($this->cipher);
    }

    /**
     * {@inheritdoc}
     */

    public function encrypt($string)
    {
        $iv = openssl_random_pseudo_bytes($this->ivSize);

        $key = $this->deriveKey($this->key, $iv, 32);

        return base64_encode($iv . openssl_encrypt($string, $this->cipher, $key, 0, $iv));
    }

    /**
     * {@inheritdoc}
     */

    public function decrypt($string)
    {
        $string = base64_decode($string, true);

        if($string === false)
        {
            return false;
        }

        $iv = substr($string, 0, $this->ivSize);

        $string = substr($string, $this->ivSize);

        $key = $this->deriveKey($this->key, $iv, 32);

        return openssl_decrypt($string, $this->cipher, $key, 0, $iv);
    }



    /**
     * Generate a PBKDF2 key derivation of a supplied key.
     *
     * @access  protected
     * @param   string     $key      The key to derive
     * @param   string     $salt     The salt
     * @param   string     $keySize  The desired key size
     * @return  string
     */

    protected function deriveKey($key, $salt, $keySize)
    {
        if(function_exists('hash_pbkdf2'))
        {
            return hash_pbkdf2(static::DERIVATION_HASH, $key, $salt, static::DERIVATION_ITERATIONS, $keySize, true);
        }
        else
        {
            $derivedKey = '';

            for($block = 1; $block <= $keySize; $block++)
            {
                $ib = $h = hash_hmac(static::DERIVATION_HASH, $salt . pack('N', $block), $key, true);

                for($i = 1; $i < static::DERIVATION_ITERATIONS; $i++)
                {
                    $ib ^= ($h = hash_hmac(static::DERIVATION_HASH, $h, $key, true));
                }

                $derivedKey .= $ib;
            }

            return substr($derivedKey, 0, $keySize);
        }
    }
}