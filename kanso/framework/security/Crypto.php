<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security;

use kanso\framework\security\crypto\encrypters\EncrypterInterface as CryptoEncrypter;
use kanso\framework\security\crypto\Signer;
use kanso\framework\security\password\encrypters\EncrypterInterface as PasswordEncrypter;

/**
 * Encryption/Decryption and password hashing.
 *
 * @author Joe J. Howard
 */
class Crypto
{
	/**
	 * Encryption/Decryption library.
	 *
	 * @var object
	 */
	private $encrytper;

    /**
     * Encryption/Decryption signer.
     *
     * @var \kanso\framework\security\crypto\Signer
     */
    private $signer;

    /**
     * Password hashing library.
     *
     * @var object
     */
    private $password;

	/**
	 * Default memory limit.
	 *
	 * @var string
	 */
	private $defaultMemory;

	/**
	 * Constructor.
	 *
	 * @param \kanso\framework\security\crypto\Signer                          $signer    Encryption/Decryption signer
	 * @param \kanso\framework\security\crypto\encrypters\EncrypterInterface   $encrytper Encryption/Decryption library
	 * @param \kanso\framework\security\password\encrypters\EncrypterInterface $password  Password hashing library
	 */
	public function __construct(Signer $signer, CryptoEncrypter $encrytper, PasswordEncrypter $password)
	{
        $this->defaultMemory = $this->getDefaultMemory();

        $this->encrytper = $encrytper;

        $this->password = $password;

        $this->signer = $signer;
	}

    /**
     * Encrypt a string.
     *
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
     * Decrypt a string.
     *
     * @param  string       $str Encrypted string to decrypt
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
	 * Get the password hasher.
	 *
	 * @return \kanso\framework\security\password\encrypters\EncrypterInterface
	 */
	public function password(): PasswordEncrypter
    {
        return $this->password;
    }

    /**
     * Get the data signer.
     *
     * @return \kanso\framework\security\crypto\Signer
     */
    public function signer(): Signer
    {
        return $this->signer;
    }

    /**
     * Get the default memory limit.
     *
     * @return string
     */
    private function getDefaultMemory()
    {
        return ini_get('memory_limit');
    }

    /**
     * Boost the memory to 1GB during encryption.
     */
    private function boostMemory(): void
    {
        ini_set('memory_limit', '1024M');
    }

    /**
     * Restore the memory after encryption.
     */
    private function restoreMemory(): void
    {
        ini_set('memory_limit', $this->defaultMemory);
    }
}
