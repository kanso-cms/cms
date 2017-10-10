<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security;

use RuntimeException;
use kanso\framework\security\crypto\Signer;
use kanso\framework\security\password\encrypters\Encrypter as passwordEncrypter;
use kanso\framework\security\crypto\encrypters\Encrypter as ctyptoEncrypter;

/**
 * Encryption/Decryption and password hashing 
 *
 * @author Joe J. Howard
 */
class Crypto
{
    /**
     * Encryption/Decryption library
     *
     * @var object
     */
	private $encrytper;

    /**
     * Encryption/Decryption signer
     *
     * @var \kanso\framework\security\crypto\Signer
     */
    private $signer;

    /**
     * Password hashing library
     *
     * @var object
     */
    private $password;

    /**
     * Default memory limit
     *
     * @var string
     */
	private $defaultMemory;

    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\security\crypto\Signer $signer     Encryption/Decryption signer
     * @param  object                                  $encrytper  Encryption/Decryption library
     * @param  object                                  $password   Password hashing library
     * @throws RuntimeException If encrypter or password objects are not extensions
     */
	public function __construct(Signer $signer, $encrytper, $password)
	{
        if (get_parent_class($encrytper) !== ctyptoEncrypter::class) 
        {
            throw new RuntimeException(vsprintf("%s(): The provided encrypter class [ %s ] must extend [ %s ]", [__METHOD__, get_class($encrytper), ctyptoEncrypter::class]));
        }

        if (get_parent_class($password) !== passwordEncrypter::class) 
        {
            throw new RuntimeException(vsprintf("%s(): The provided password hashing class [ %s ] must extend [ %s ]", [__METHOD__, get_class($password), passwordEncrypter::class]));
        }

        $this->defaultMemory = $this->getDefaultMemory();

        $this->encrytper = $encrytper;

        $this->password = $password;

        $this->signer = $signer;
	}

    /**
     * Encrypt a string
     *
     * @access public
     * @param  string $str String to encrypt
     * @return string
     */
    public function encrypt(string $str): string
    {
        $this->boostMemory();
        
        $data = $this->signer->sign($this->encrytper->encrypt($str));
        
        $this->restoreMemory();
        
        return $data;
    }
	
    /**
     * Decrypt a string
     *
     * @access public
     * @param  string $str Encrypted string to decrypt
     * @return string|false
     */
    public function decrypt(string $str)
    {
        $this->boostMemory();
        
        $unsigned = $this->signer->validate($str);
        
        if (!$unsigned)
        {
            return false;
        }
        
        $decrypt = $this->encrytper->decrypt($unsigned);
        
        $this->restoreMemory();
        
        return $decrypt;
    }

    /**
     * Get the password hasher
     *
     * @access public
     * @return object
     */
	public function password()
    {
        return $this->password;
    }

    /**
     * Get the default memory limit
     *
     * @access private
     * @return string
     */
    private function getDefaultMemory()
    {
        return ini_get('memory_limit');
    }

    /**
     * Boost the memory to 1GB during encryption
     *
     * @access private
     */
    private function boostMemory()
    {
        ini_set('memory_limit', '1024M');
    }

    /**
     * Restore the memory after encryption
     *
     * @access private
     */
    private function restoreMemory()
    {
        ini_set('memory_limit', $this->defaultMemory);
    }
}
